<?php
session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Phpml\Classification\NaiveBayes;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\FeatureExtraction\TfIdfTransformer;

/* ===============================
    1. DATABASE & SESSION SETUP
================================ */
$conn = mysqli_connect("localhost", "root", "", "portal-asisstant-ai");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

$sess_id = session_id(); 
$user_input = strtolower(trim($_POST['message'] ?? ''));
if (empty($user_input)) { exit; }

/* ============================================================
    2. PRIORITY 1: KNOWLEDGE BASE CHECK (The "Learned" Brain)
============================================================ */
$check_kb = $conn->prepare("SELECT verified_answer FROM ai_knowledge_base WHERE student_query LIKE ? LIMIT 1");
$search_term = "%$user_input%";
$check_kb->bind_param("s", $search_term);
$check_kb->execute();
$kb_result = $check_kb->get_result();

if ($kb_result->num_rows > 0) {
    $row = $kb_result->fetch_assoc();
    $reply = $row['verified_answer'];
    
    // Log history
    $stmt_msg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'student', ?)");
    $stmt_msg->bind_param("ss", $sess_id, $user_input);
    $stmt_msg->execute();

    $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
    $stmt_bot->bind_param("ss", $sess_id, $reply);
    $stmt_bot->execute();

    echo "<b>Verified Support:</b> " . $reply;
    exit; 
}

/* ===============================
    3. IMPROVED INTENT DETECTION WITH PRIORITY CHECK
================================ */
function detectIntent($input) {
    // Check for year level queries (HIGHEST PRIORITY for workload)
    $year_patterns = [
        '/first\s*year/i' => 'First Year',
        '/second\s*year/i' => 'Second Year',
        '/third\s*year/i' => 'Third Year',
        '/fourth\s*year/i' => 'Fourth Year',
        '/1st\s*year/i' => 'First Year',
        '/2nd\s*year/i' => 'Second Year',
        '/3rd\s*year/i' => 'Third Year',
        '/4th\s*year/i' => 'Fourth Year',
        '/year\s*1/i' => 'First Year',
        '/year\s*2/i' => 'Second Year',
        '/year\s*3/i' => 'Third Year',
        '/year\s*4/i' => 'Fourth Year'
    ];
    
    foreach ($year_patterns as $pattern => $year) {
        if (preg_match($pattern, $input)) {
            return ['intent' => 'view_all', 'year' => $year];
        }
    }
    
    // Check for semester patterns with year
    if (preg_match('/([1-4])\.([1-2])/', $input, $matches)) {
        $year_map = ['1' => 'First Year', '2' => 'Second Year', '3' => 'Third Year', '4' => 'Fourth Year'];
        $sem_map = ['1' => '1st Semester', '2' => '2nd Semester'];
        return ['intent' => 'view_all', 'year' => $year_map[$matches[1]], 'semester' => $sem_map[$matches[2]]];
    }
    
    // Check for unit search
    if (preg_match('/unit|course|what is|tell me about/i', $input) && preg_match('/[A-Z]{3,4}[0-9]{4}/i', $input)) {
        return ['intent' => 'search_unit'];
    }
    
    // Timetable keywords
    $timetable_keywords = ['class', 'schedule', 'timetable', 'lecture', 'when is my class', 'what class', 'this week', 'today\'s class', 'classes for', 'my classes', 'class schedule'];
    foreach ($timetable_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'timetable'];
        }
    }
    
    // Exam keywords
    $exam_keywords = ['exam', 'test', 'assessment', 'when is exam', 'exam date'];
    foreach ($exam_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'exam_info'];
        }
    }
    
    // Student info keywords
    $student_keywords = ['who am i', 'my name', 'my details', 'my info', 'my registration', 'my email'];
    foreach ($student_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'student_info'];
        }
    }
    
    // My courses keywords
    $courses_keywords = ['my courses', 'my units', 'registered courses', 'what am i taking', 'enrolled in'];
    foreach ($courses_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'my_courses'];
        }
    }
    
    // Greetings
    $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'];
    if (in_array($input, $greetings) || preg_match('/^(hi|hello|hey)/i', $input)) {
        return ['intent' => 'greet'];
    }
    
    return null;
}

// Check for priority intents first
$priority_check = detectIntent($user_input);
if ($priority_check) {
    $intent = $priority_check['intent'];
    // Store additional data for the intent
    if (isset($priority_check['year'])) {
        $GLOBALS['target_year'] = $priority_check['year'];
    }
    if (isset($priority_check['semester'])) {
        $GLOBALS['target_semester'] = $priority_check['semester'];
    }
} else {
    // Use ML for other intents
    $samples = [
        'hi', 'hello', 'hey', 'good morning',
        'units', 'workload', 
        'math', 'programming', 'database',
        'registration', 'register', 'enroll',
        'ai', 'artificial intelligence',
        'conversation', 'chat history',
        'referral', 'my referrals'
    ];

    $labels = [
        'greet', 'greet', 'greet', 'greet',
        'view_all', 'view_all',
        'search_unit', 'search_unit', 'search_unit',
        'reg_help', 'reg_help', 'reg_help',
        'ai_check', 'ai_check',
        'chat_history', 'chat_history',
        'referral_info', 'referral_info'
    ];

    $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
    $vectorizer->fit($samples);
    $vectorizer->transform($samples);
    $transformer = new TfIdfTransformer($samples);
    $transformer->transform($samples);
    $classifier = new NaiveBayes();
    $classifier->train($samples, $labels);

    $test_sample = [$user_input];
    $vectorizer->transform($test_sample);
    $transformer->transform($test_sample);
    $intent = $classifier->predict($test_sample)[0];
}

/* ===============================
    4. ACTION LOGIC (FIXED - NO day_of_week)
================================ */
function formatResponse($data, $title) {
    if (empty($data)) {
        return "No information found for your query.";
    }
    
    $response = "<b>$title</b><br>";
    foreach ($data as $item) {
        $response .= "• " . implode(" - ", $item) . "<br>";
    }
    return $response;
}

switch ($intent) {
    case 'greet':
        $student_name = $_SESSION['user_name'] ?? '';
        if ($student_name) {
            echo "Hello $student_name! I'm your AI assistant. I can help you with:<br>
                  • Viewing units by year level (e.g., 'Show me second year units')<br>
                  • Checking your timetable<br>
                  • Exam schedules<br>
                  • Registered courses<br>
                  What would you like to know?";
        } else {
            echo "Hello! I'm your AI assistant. I can help you with:<br>
                  • Viewing units by year level (e.g., 'Show me second year units')<br>
                  • Checking your timetable<br>
                  • Exam schedules<br>
                  What would you like to know?";
        }
        break;

    case 'view_all':
        // Get the target year from global or extract from input
        $target_year = $GLOBALS['target_year'] ?? null;
        $target_semester = $GLOBALS['target_semester'] ?? null;
        
        // If not set via priority detection, try to extract from input
        if (!$target_year) {
            $year_patterns = [
                'first year' => 'First Year',
                'second year' => 'Second Year',
                'third year' => 'Third Year',
                'fourth year' => 'Fourth Year',
                '1st year' => 'First Year',
                '2nd year' => 'Second Year',
                '3rd year' => 'Third Year',
                '4th year' => 'Fourth Year'
            ];
            
            foreach ($year_patterns as $pattern => $year) {
                if (strpos($user_input, $pattern) !== false) {
                    $target_year = $year;
                    break;
                }
            }
        }
        
        if (!$target_year) {
            // If no specific year, show all units summary
            $all_units = mysqli_query($conn, "SELECT year_level, COUNT(*) as count FROM academic_workload GROUP BY year_level");
            $summary = [];
            while ($row = mysqli_fetch_assoc($all_units)) {
                $summary[] = [$row['year_level'] . ": " . $row['count'] . " units"];
            }
            echo formatResponse($summary, "Units by Year Level:");
            echo "<br>To see units for a specific year, ask: 'Show me first year units' or 'Second year workload'";
            break;
        }
        
        // Build query based on semester if specified
        if ($target_semester) {
            $stmt = $conn->prepare("SELECT unit_code, unit_name, semester_level, offering_time 
                                    FROM academic_workload 
                                    WHERE year_level = ? AND semester_level = ? 
                                    ORDER BY unit_code");
            $stmt->bind_param("ss", $target_year, $target_semester);
        } else {
            // Show all semesters for that year
            $stmt = $conn->prepare("SELECT unit_code, unit_name, semester_level, offering_time 
                                    FROM academic_workload 
                                    WHERE year_level = ? 
                                    ORDER BY semester_level, unit_code");
            $stmt->bind_param("s", $target_year);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $units_by_semester = [];
            while ($row = $result->fetch_assoc()) {
                $semester = $row['semester_level'];
                if (!isset($units_by_semester[$semester])) {
                    $units_by_semester[$semester] = [];
                }
                $units_by_semester[$semester][] = $row['unit_code'] . " - " . $row['unit_name'] . " (" . $row['offering_time'] . ")";
            }
            
            echo "<b>$target_year Units:</b><br>";
            foreach ($units_by_semester as $semester => $units) {
                echo "<br><b>$semester:</b><br>";
                foreach ($units as $unit) {
                    echo "• $unit<br>";
                }
            }
        } else {
            echo "No units found for $target_year. Please check the academic workload table.";
        }
        break;

    case 'search_unit':
        // Extract unit code from input
        preg_match('/[A-Z]{3,4}[0-9]{4}/i', $user_input, $matches);
        $search_term = isset($matches[0]) ? $matches[0] : $user_input;
        
        $search_pattern = "%$search_term%";
        $stmt = $conn->prepare("SELECT unit_code, unit_name, year_level, semester_level, offering_time 
                                FROM academic_workload 
                                WHERE unit_code LIKE ? OR unit_name LIKE ? 
                                LIMIT 5");
        $stmt->bind_param("ss", $search_pattern, $search_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $results = [];
            while ($row = $result->fetch_assoc()) {
                $results[] = [
                    $row['unit_code'],
                    $row['unit_name'],
                    $row['year_level'] . " - " . $row['semester_level'],
                    "Offered: " . $row['offering_time']
                ];
            }
            echo formatResponse($results, "Found matching units:");
        } else {
            echo "No units found matching '$search_term'. Try checking the unit code or name.";
        }
        break;

    case 'timetable':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if ($student_reg) {
            $current_semester = '1'; // Default to first semester
            $current_year = date('Y');
            
            // Get student's registered courses with timetable information
            $query = "SELECT t.unit_code, t.course_title, t.time_from, t.time_to, t.venue, t.lecturer 
                     FROM timetable t 
                     INNER JOIN registered_courses rc ON t.unit_code = rc.unit_code 
                     WHERE rc.student_reg_no = ? AND t.semester = ? AND t.academic_year = ?
                     ORDER BY t.time_from";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $student_reg, $current_semester, $current_year);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<b>Your Class Schedule:</b><br><br>";
                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<b>{$counter}. {$row['unit_code']} - {$row['course_title']}</b><br>";
                    echo "   ⏰ Time: {$row['time_from']} - {$row['time_to']}<br>";
                    echo "   📍 Venue: {$row['venue']}<br>";
                    echo "   👨‍🏫 Lecturer: {$row['lecturer']}<br><br>";
                    $counter++;
                }
            } else {
                echo "You don't have any classes scheduled for this semester. Make sure you're registered for courses and the timetable has been published.";
            }
        } else {
            echo "Please log in to view your class schedule.";
        }
        break;

    case 'exam_info':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if ($student_reg) {
            $query = "SELECT t.unit_code, t.course_title, t.exam_date, t.venue 
                     FROM timetable t 
                     INNER JOIN registered_courses rc ON t.unit_code = rc.unit_code 
                     WHERE rc.student_reg_no = ? AND t.exam_date IS NOT NULL AND t.exam_date != '0000-00-00'
                     ORDER BY t.exam_date";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $student_reg);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $results = [];
                while ($row = $result->fetch_assoc()) {
                    $exam_date = ($row['exam_date'] != '0000-00-00') ? date('F j, Y', strtotime($row['exam_date'])) : 'Date TBA';
                    $results[] = [
                        $row['unit_code'] . " - " . $row['course_title'],
                        "Date: " . $exam_date,
                        "Venue: " . ($row['venue'] ?: 'TBA')
                    ];
                }
                echo formatResponse($results, "Your upcoming exams:");
            } else {
                echo "No exam dates have been scheduled for your registered courses yet.";
            }
        } else {
            echo "Please log in to view your exam schedule.";
        }
        break;

    case 'my_courses':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if ($student_reg) {
            $query = "SELECT rc.unit_code, aw.unit_name, rc.status, rc.academic_year, aw.year_level, aw.semester_level
                     FROM registered_courses rc 
                     JOIN academic_workload aw ON rc.unit_code = aw.unit_code 
                     WHERE rc.student_reg_no = ? 
                     ORDER BY aw.year_level, aw.semester_level";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $student_reg);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $results = [];
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        $row['unit_code'],
                        $row['unit_name'],
                        $row['year_level'] . " - " . $row['semester_level'],
                        "Status: " . $row['status']
                    ];
                }
                echo formatResponse($results, "Your registered courses:");
            } else {
                echo "You haven't registered for any courses yet. Please complete your registration.";
            }
        } else {
            echo "Please log in to view your registered courses.";
        }
        break;

    case 'student_info':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if ($student_reg) {
            $query = "SELECT full_name, reg_number, email, department FROM users WHERE reg_number = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $student_reg);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<b>Your Information:</b><br>";
                echo "• Name: " . $row['full_name'] . "<br>";
                echo "• Registration Number: " . $row['reg_number'] . "<br>";
                echo "• Email: " . $row['email'] . "<br>";
                echo "• Department: " . $row['department'] . "<br>";
            } else {
                echo "Student information not found.";
            }
        } else {
            echo "Please log in to view your information.";
        }
        break;

    case 'reg_help':
        echo "To register for courses:<br>
              1. Go to the registration section<br>
              2. Select the units for your year level<br>
              3. Confirm your registration<br>
              <br>
              Need help with specific units? Just ask me about them!";
        break;

    case 'ai_check':
        echo "I'm an AI-powered academic assistant that can:<br>
              • Show units by year level (e.g., 'Show me second year units')<br>
              • Search for specific units by code or name<br>
              • Display your timetable (login required)<br>
              • Show your exam schedule (login required)<br>
              • List your registered courses (login required)<br>
              <br>
              What would you like to know?";
        break;

    case 'fallback':
    default:
        fallback_logic:
        // Save user message
        $stmt_msg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'student', ?)");
        $stmt_msg->bind_param("ss", $sess_id, $user_input);
        $stmt_msg->execute();

        $student_name = $_SESSION['user_name'] ?? 'Guest Student';
        
        // Check if already referred
        $check_ref = $conn->prepare("SELECT id FROM admin_referrals WHERE conversation_id = ? AND status = 'pending'");
        $check_ref->bind_param("s", $sess_id);
        $check_ref->execute();
        
        if ($check_ref->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO admin_referrals (sender_name, status, conversation_id) VALUES (?, 'pending', ?)");
            $stmt->bind_param("ss", $student_name, $sess_id);
            $stmt->execute();
            $bot_msg = "I'm not sure about that. I've forwarded your query to the Admin inbox for you!";
        } else {
            $bot_msg = "I've already forwarded your previous query to the admin. They will respond soon.";
        }

        echo $bot_msg;
        
        $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
        $stmt_bot->bind_param("ss", $sess_id, $bot_msg);
        $stmt_bot->execute();
        break; 
}
?>