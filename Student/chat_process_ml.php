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

/* ===============================
    2. DICTIONARY / VOCABULARY FUNCTIONS
================================ */
function getWordDefinition($word, $conn) {
    $word = trim(strtolower(preg_replace('/[^a-zA-Z\s-]/', '', $word)));
    if (strlen($word) < 2) { return null; }
    
    $local_def = getLocalDefinition($word, $conn);
    if ($local_def) { return $local_def; }
    
    $api_def = getAPIDefinition($word);
    if ($api_def) {
        saveToVocabulary($word, $api_def, $conn);
        return $api_def;
    }
    return null;
}

function getLocalDefinition($word, $conn) {
    $stmt = $conn->prepare("SELECT word, part_of_speech, definition, example, synonyms FROM vocabulary WHERE word = ? LIMIT 1");
    $stmt->bind_param("s", $word);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $output = "<b>📚 Definition of \"{$row['word']}\":</b><br><br>";
        $output .= "<b>{$row['part_of_speech']}:</b> {$row['definition']}<br>";
        if ($row['example']) { $output .= "<i>Example: \"{$row['example']}\"</i><br>"; }
        if ($row['synonyms']) { $output .= "<b>Synonyms:</b> {$row['synonyms']}<br>"; }
        return $output;
    }
    return null;
}

function getAPIDefinition($word) {
    $api_url = "https://api.dictionaryapi.dev/api/v2/entries/en/" . urlencode($word);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Academic Assistant Bot/1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && $response) {
        $data = json_decode($response, true);
        if (!empty($data) && isset($data[0])) {
            $word_data = $data[0];
            $word_name = $word_data['word'];
            $meanings = $word_data['meanings'];
            $result = "<b>📚 Definition of \"{$word_name}\":</b><br><br>";
            foreach (array_slice($meanings, 0, 2) as $meaning) {
                $part_of_speech = $meaning['partOfSpeech'];
                $result .= "<b>{$part_of_speech}:</b><br>";
                $definitions = array_slice($meaning['definitions'], 0, 2);
                foreach ($definitions as $index => $def) {
                    $result .= "  " . ($index + 1) . ". {$def['definition']}<br>";
                    if (isset($def['example'])) {
                        $result .= "     <i>Example: \"{$def['example']}\"</i><br>";
                    }
                }
                $result .= "<br>";
            }
            if (isset($meanings[0]['synonyms']) && !empty($meanings[0]['synonyms'])) {
                $synonyms = array_slice($meanings[0]['synonyms'], 0, 5);
                if (!empty($synonyms)) {
                    $result .= "<b>Synonyms:</b> " . implode(", ", $synonyms) . "<br>";
                }
            }
            return $result;
        }
    }
    return null;
}

function saveToVocabulary($word, $definition_html, $conn) {
    $plain_definition = strip_tags($definition_html);
    $part_of_speech = 'noun';
    $definition_text = $plain_definition;
    $example = '';
    $synonyms = '';
    
    if (preg_match('/Example: "([^"]+)"/', $plain_definition, $matches)) {
        $example = $matches[1];
        $definition_text = preg_replace('/Example: "[^"]+"/', '', $definition_text);
    }
    if (preg_match('/Synonyms: (.+)/', $plain_definition, $matches)) {
        $synonyms = $matches[1];
        $definition_text = preg_replace('/Synonyms: .+/', '', $definition_text);
    }
    $definition_text = trim(preg_replace('/\s+/', ' ', $definition_text));
    
    $stmt = $conn->prepare("INSERT INTO vocabulary (word, part_of_speech, definition, example, synonyms) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE definition = VALUES(definition)");
    $stmt->bind_param("sssss", $word, $part_of_speech, $definition_text, $example, $synonyms);
    $stmt->execute();
}

function detectVocabularyIntent($input) {
    $vocab_keywords = ['meaning', 'define', 'what does', 'what is', 'definition of', 'vocabulary', 'word', 'means'];
    foreach ($vocab_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) { return true; }
    }
    return false;
}

function extractWordFromQuery($input) {
    $input = preg_replace('/what (is|does|are) /i', '', $input);
    $input = preg_replace('/define|meaning of|definition of|vocabulary|word|means?/i', '', $input);
    $input = trim($input);
    $words = explode(' ', $input);
    if (count($words) > 1) {
        if (strpos($input, 'artificial') !== false && strpos($input, 'intelligence') !== false) { return 'artificial intelligence'; }
        if (strpos($input, 'machine') !== false && strpos($input, 'learning') !== false) { return 'machine learning'; }
        return end($words);
    }
    return $input;
}

/* ============================================================
    3. FUZZY MATCHING FUNCTIONS
============================================================ */
function fuzzyMatch($input, $target, $threshold = 70) {
    $input = strtolower(trim($input));
    $target = strtolower(trim($target));
    if ($input === $target) { return true; }
    similar_text($input, $target, $percent);
    $distance = levenshtein($input, $target);
    $max_len = max(strlen($input), strlen($target));
    $lev_percent = ($max_len > 0) ? (1 - ($distance / $max_len)) * 100 : 0;
    $similarity = max($percent, $lev_percent);
    return $similarity >= $threshold;
}

function getClosestMatch($input, $options, $threshold = 70) {
    $best_match = null;
    $best_score = 0;
    foreach ($options as $option) {
        $input = strtolower(trim($input));
        $option = strtolower(trim($option));
        similar_text($input, $option, $percent);
        $distance = levenshtein($input, $option);
        $max_len = max(strlen($input), strlen($option));
        $lev_percent = ($max_len > 0) ? (1 - ($distance / $max_len)) * 100 : 0;
        $score = max($percent, $lev_percent);
        if ($score > $best_score && $score >= $threshold) {
            $best_score = $score;
            $best_match = $option;
        }
    }
    return $best_match;
}

function fuzzySearchUnits($search_term, $conn, $limit = 5) {
    $query = "SELECT unit_code, unit_name FROM academic_workload";
    $result = mysqli_query($conn, $query);
    $matches = [];
    $search = strtolower(trim($search_term));
    while ($row = mysqli_fetch_assoc($result)) {
        $unit_code = strtolower($row['unit_code']);
        $unit_name = strtolower($row['unit_name']);
        similar_text($search, $unit_code, $code_similarity);
        $code_distance = levenshtein($search, $unit_code);
        $code_max_len = max(strlen($search), strlen($unit_code));
        $code_lev_percent = ($code_max_len > 0) ? (1 - ($code_distance / $code_max_len)) * 100 : 0;
        $code_score = max($code_similarity, $code_lev_percent);
        similar_text($search, $unit_name, $name_similarity);
        $name_distance = levenshtein($search, $unit_name);
        $name_max_len = max(strlen($search), strlen($unit_name));
        $name_lev_percent = ($name_max_len > 0) ? (1 - ($name_distance / $name_max_len)) * 100 : 0;
        $name_score = max($name_similarity, $name_lev_percent);
        $score = max($code_score, $name_score);
        if ($score >= 60) {
            $matches[] = [
                'unit_code' => $row['unit_code'],
                'unit_name' => $row['unit_name'],
                'score' => $score,
                'matched_on' => ($code_score > $name_score) ? 'code' : 'name'
            ];
        }
    }
    usort($matches, function($a, $b) { return $b['score'] <=> $a['score']; });
    return array_slice($matches, 0, $limit);
}

/* ============================================================
    4. UNIT FILTERING & REGISTRATION FUNCTIONS
============================================================ */

// Define the official units to register per year and semester (6 units per semester)
function getUnitsToRegister($year_level, $semester_num) {
    $unitsMapping = [
        'First Year_1' => ['BAF1101', 'BIT1102', 'BBM1101', 'BIT1106', 'BMA1106', 'BUCU007'],
        'First Year_2' => ['BBM1201', 'BIT1101', 'BIT1201', 'BIT2102', 'BMA1104', 'BUCU008'],
        'Second Year_1' => ['BBM2103', 'BEG2112', 'BBM1202', 'BIT2205', 'BMA2102', 'BUCU010'],
        'Second Year_2' => ['BIT2206', 'BIT3101', 'BIT3102', 'BMA3102', 'BUCU009', 'BIT3105'],
        'Third Year_1' => ['BIT3205', 'BIT3206', 'BIT3201', 'BIT3204', 'BIT3208', 'BMA3201'],
        'Third Year_2' => ['BIT3224', 'BIT4104', 'BIT4101', 'BIT4105', 'BIT4202', 'BIT4102'],
        'Fourth Year_1' => ['BIT4201', 'BIT4203', 'BIT4204', 'BIT4205', 'BIT4206', 'BIT4217'],
        'Fourth Year_2' => ['BIT4103', 'BIT4107', 'BIT4108', 'BIT4209', 'BBM3107']
    ];
    $key = $year_level . '_' . $semester_num;
    return $unitsMapping[$key] ?? [];
}

// Get student's current year level based on registered courses
function getStudentYearLevel($conn, $student_reg) {
    $query = "SELECT DISTINCT t.year_level 
              FROM registered_courses rc 
              JOIN timetable t ON rc.unit_code = t.unit_code 
              WHERE rc.student_reg_no = ? 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_reg);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['year_level'];
    }
    
    return 'First Year';
}

// Get current semester based on month
function getCurrentSemester() {
    $current_month = date('n');
    return ($current_month >= 1 && $current_month <= 6) ? 1 : 2;
}

// Get semester name
function getSemesterName($semester_num) {
    return ($semester_num == 1) ? '1st Semester' : '2nd Semester';
}

// Get the timetable directly from database using year_level and semester
function getStudentTimetable($conn, $year_level, $semester_num) {
    $query = "SELECT unit_code, course_title, day_of_week, time_from, time_to, venue, lecturer 
              FROM timetable 
              WHERE year_level = ? AND semester = ? 
              ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), time_from";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $year_level, $semester_num);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $timetable = [];
    while ($row = $result->fetch_assoc()) {
        $timetable[] = $row;
    }
    return $timetable;
}

// Get unit titles for given unit codes
function getUnitTitles($conn, $unit_codes) {
    if (empty($unit_codes)) return [];
    
    $placeholders = implode(',', array_fill(0, count($unit_codes), '?'));
    $types = str_repeat('s', count($unit_codes));
    $query = "SELECT unit_code, course_title FROM timetable WHERE unit_code IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$unit_codes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $titles = [];
    while ($row = $result->fetch_assoc()) {
        $titles[$row['unit_code']] = $row['course_title'];
    }
    return $titles;
}

// Check what units the student has already registered (WITH COURSE NAMES)
function getStudentRegisteredUnits($conn, $student_reg, $year_level, $semester_num) {
    $required_units = getUnitsToRegister($year_level, $semester_num);
    
    if (empty($required_units)) {
        return ['error' => 'No required units found for this semester'];
    }
    
    $placeholders = implode(',', array_fill(0, count($required_units), '?'));
    $types = str_repeat('s', count($required_units));
    
    $query = "SELECT unit_code FROM registered_courses 
              WHERE student_reg_no = ? AND unit_code IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $params = array_merge([$student_reg], $required_units);
    $stmt->bind_param("s" . $types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $registered = [];
    while ($row = $result->fetch_assoc()) {
        $registered[] = $row['unit_code'];
    }
    
    $not_registered = array_diff($required_units, $registered);
    
    // Get course titles for all required units
    $unit_titles = getUnitTitles($conn, $required_units);
    
    // Build arrays with names
    $required_with_names = [];
    $registered_with_names = [];
    $not_registered_with_names = [];
    
    foreach ($required_units as $unit) {
        $unit_name = $unit_titles[$unit] ?? 'Unit Name Not Found';
        $required_with_names[] = [
            'code' => $unit,
            'name' => $unit_name,
            'full' => $unit . ' - ' . $unit_name
        ];
        
        if (in_array($unit, $registered)) {
            $registered_with_names[] = [
                'code' => $unit,
                'name' => $unit_name,
                'full' => $unit . ' - ' . $unit_name
            ];
        } else {
            $not_registered_with_names[] = [
                'code' => $unit,
                'name' => $unit_name,
                'full' => $unit . ' - ' . $unit_name
            ];
        }
    }
    
    return [
        'required' => $required_units,
        'required_with_names' => $required_with_names,
        'registered' => $registered,
        'registered_with_names' => $registered_with_names,
        'not_registered' => $not_registered,
        'not_registered_with_names' => $not_registered_with_names,
        'total_required' => count($required_units),
        'total_registered' => count($registered),
        'completion_percentage' => count($required_units) > 0 ? (count($registered) / count($required_units)) * 100 : 0
    ];
}

// Format timetable for display (UPDATED with download link)
function displayTimetable($timetable, $year_level, $semester_name, $student_reg) {
    if (empty($timetable)) {
        return "<b>⚠️ No timetable found for {$year_level}, {$semester_name}</b><br><br>
                💡 <i>Please contact the academic office.</i>";
    }
    
    $output = "<b>📅 Your {$year_level} - {$semester_name} Timetable</b><br>";
    $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
    
    $counter = 1;
    foreach ($timetable as $unit) {
        $output .= "<b>{$counter}. {$unit['unit_code']} - {$unit['course_title']}</b><br>";
        $output .= "   📆 <b>Day:</b> " . ($unit['day_of_week'] ?? 'TBA') . "<br>";
        $output .= "   ⏰ <b>Time:</b> " . ($unit['time_from'] ?? 'TBA') . " - " . ($unit['time_to'] ?? 'TBA') . "<br>";
        $output .= "   📍 <b>Venue:</b> " . ($unit['venue'] ?? 'TBA') . "<br>";
        $output .= "   👨‍🏫 <b>Lecturer:</b> " . ($unit['lecturer'] ?? 'TBA') . "<br><br>";
        $counter++;
    }
    
    $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
    $output .= "✅ <i>These are the 6 required units for this semester.</i><br><br>";
    
    // Generate download link
    $download_token = base64_encode($student_reg . '_' . time());
    $download_url = "download_timetable.php?token=" . urlencode($download_token) . "&student=" . urlencode($student_reg);
    
    $output .= "📥 <b>Download Your Timetable:</b><br>";
    $output .= "🔗 <a href='{$download_url}' target='_blank' style='background-color: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>📄 Download Timetable (PDF/HTML)</a><br><br>";
    
    $output .= "💡 <i>You can also say 'Download my timetable' to get the link again.</i><br>";
    
    return $output;
}

/* ===============================
    5. FUZZY INTENT DETECTION (UPDATED)
================================ */
function fuzzyDetectIntent($input) {
    $intent_patterns = [
        'greet' => ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'],
        'farewell' => ['bye', 'goodbye', 'see you', 'later', 'ciao', 'farewell'],
        'gratitude' => ['thank', 'thanks', 'appreciate', 'grateful', 'thx'],
        'how_are_you' => ['how are you', 'how do you do', 'how are you doing', 'how is it going', 'whats up'],
        'my_courses' => ['my courses', 'my units', 'registered courses', 'what am i taking', 'enrolled in', 'my classes'],
        'reg_help' => ['how to register', 'register for units', 'registration process', 'how do i register', 'enroll', 'add units'],
        'timetable' => ['timetable', 'schedule', 'class schedule', 'my classes', 'lecture schedule', 'time table', 'show me my timetable', 'my timetable', 'my class schedule', 'show timetable', 'show schedule', 'my schedule', 'class timetable'],
        'download_timetable' => ['download timetable', 'download my timetable', 'get timetable pdf', 'save timetable', 'export timetable', 'pdf timetable', 'download schedule'],
        'view_all' => ['show me', 'view units', 'list units', 'units for', 'courses for', 'first year', 'second year', 'third year', 'fourth year'],
        'exam_info' => ['exam', 'test', 'assessment', 'exam schedule', 'exam date', 'when is exam'],
        'student_info' => ['who am i', 'my details', 'my info', 'my profile', 'my registration number'],
        'search_unit' => ['search unit', 'find unit', 'look up', 'unit details', 'course details', 'tell me about'],
        'ai_check' => ['what can you do', 'help', 'capabilities', 'what do you do', 'features', 'how can you help'],
        'unit_day' => ['when is', 'what day', 'what time', 'schedule for', 'class for', 'taught on'],
        'course_advice' => ['what courses should i take', 'which units should i register', 'advice on courses', 'recommend units', 'suggest courses', 'what to register', 'help me choose units'],
        'unit_registration_count' => ['how many students', 'how many registered', 'enrollment count', 'class size', 'student count', 'enrollment numbers'],
        'what_to_register' => ['what to register', 'which units should i take', 'units to register', 'required units', 'what units do i need', 'units i should take', 'my required units', 'what am i supposed to register', 'what units should i register'],
        'registration_status' => ['registration status', 'my registration status', 'have i registered', 'check my registration', 'registered units', 'what have i registered for', 'am i fully registered', 'missing units']
    ];
    
    $input_lower = strtolower(trim($input));
    $best_intent = null;
    $best_score = 0;
    
    foreach ($intent_patterns as $intent => $patterns) {
        foreach ($patterns as $pattern) {
            if (strpos($input_lower, $pattern) !== false) {
                return $intent;
            }
        }
    }
    
    foreach ($intent_patterns as $intent => $patterns) {
        foreach ($patterns as $pattern) {
            if (strlen($pattern) > 5) {
                similar_text($input_lower, $pattern, $similarity);
                if ($similarity > $best_score && $similarity > 70) {
                    $best_score = $similarity;
                    $best_intent = $intent;
                }
            }
        }
    }
    
    if (!$best_intent) {
        $words = explode(' ', $input_lower);
        foreach ($words as $word) {
            foreach ($intent_patterns as $intent => $patterns) {
                foreach ($patterns as $pattern) {
                    if (fuzzyMatch($word, $pattern, 75)) {
                        return $intent;
                    }
                }
            }
        }
    }
    
    return $best_intent;
}

/* ===============================
    6. PRIORITY 1: KNOWLEDGE BASE CHECK
================================ */
$check_kb = $conn->prepare("SELECT verified_answer FROM ai_knowledge_base WHERE student_query LIKE ? LIMIT 1");
$search_term = "%$user_input%";
$check_kb->bind_param("s", $search_term);
$check_kb->execute();
$kb_result = $check_kb->get_result();

if ($kb_result->num_rows > 0) {
    $row = $kb_result->fetch_assoc();
    $reply = $row['verified_answer'];
    
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
    7. PRIORITY 2: VOCABULARY CHECK
================================ */
$vocabulary_check = detectVocabularyIntent($user_input);

if ($vocabulary_check) {
    $word_to_define = extractWordFromQuery($user_input);
    $definition = getWordDefinition($word_to_define, $conn);
    
    if ($definition) {
        echo $definition;
        $followups = [
            "📖 Want me to explain another word?",
            "💡 Would you like to see how to use this word in a sentence?",
            "🎓 Need help with any other academic vocabulary?",
            "📚 I can help you build your vocabulary! Ask me about any word."
        ];
        echo "<br><br><i>" . getRandomResponse($followups) . "</i>";
        
        $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
        $definition_short = substr($definition, 0, 500);
        $stmt_bot->bind_param("ss", $sess_id, $definition_short);
        $stmt_bot->execute();
        exit;
    } else {
        echo "🤔 I'm still learning the word '{$word_to_define}'!<br><br>";
        echo "💡 <i>Tip: Try asking about academic terms like 'algorithm', 'database', 'semester', or 'curriculum'!</i><br><br>";
        echo "📝 I've noted this word and will add it to my vocabulary soon!";
        
        $requested_by = $_SESSION['user_name'] ?? 'Guest';
        $stmt = $conn->prepare("INSERT INTO vocabulary_requests (word, requested_by) VALUES (?, ?)");
        $stmt->bind_param("ss", $word_to_define, $requested_by);
        $stmt->execute();
        exit;
    }
}

/* ===============================
    8. SOCIAL INTENT DETECTION
================================ */
function detectSocialIntent($input) {
    $gratitude = ['thank', 'thanks', 'thnx', 'thank you', 'appreciate', 'grateful', 'thx'];
    foreach ($gratitude as $word) {
        if (strpos($input, $word) !== false) { return 'gratitude'; }
    }
    $apologies = ['sorry', 'apologize', 'my bad', 'apologies'];
    foreach ($apologies as $word) {
        if (strpos($input, $word) !== false) { return 'apology'; }
    }
    $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'howdy', 'yo'];
    foreach ($greetings as $word) {
        if (strpos($input, $word) !== false) { return 'greet'; }
    }
    $farewell = ['bye', 'goodbye', 'see you', 'later', 'ciao', 'farewell', 'talk to you later'];
    foreach ($farewell as $word) {
        if (strpos($input, $word) !== false) { return 'farewell'; }
    }
    $how_are_you = ['how are you', 'how are you doing', 'how\'s it going', 'how are things', 'what\'s up', 'sup'];
    foreach ($how_are_you as $phrase) {
        if (strpos($input, $phrase) !== false) { return 'how_are_you'; }
    }
    $compliments = ['you are great', 'you\'re awesome', 'good bot', 'nice', 'cool', 'amazing'];
    foreach ($compliments as $word) {
        if (strpos($input, $word) !== false) { return 'compliment'; }
    }
    return null;
}

/* ===============================
    9. ACADEMIC INTENT DETECTION (UPDATED)
================================ */
function detectIntent($input) {
    $fuzzy_intent = fuzzyDetectIntent($input);
    if ($fuzzy_intent) {
        switch ($fuzzy_intent) {
            case 'reg_help': return ['intent' => 'reg_help'];
            case 'my_courses': return ['intent' => 'my_courses'];
            case 'timetable': return ['intent' => 'timetable'];
            case 'download_timetable': return ['intent' => 'download_timetable'];
            case 'exam_info': return ['intent' => 'exam_info'];
            case 'student_info': return ['intent' => 'student_info'];
            case 'ai_check': return ['intent' => 'ai_check'];
            case 'greet': return ['intent' => 'greet'];
            case 'farewell': return ['intent' => 'farewell'];
            case 'gratitude': return ['intent' => 'gratitude'];
            case 'how_are_you': return ['intent' => 'how_are_you'];
            case 'course_advice': return ['intent' => 'course_advice'];
            case 'what_to_register': return ['intent' => 'what_to_register'];
            case 'registration_status': return ['intent' => 'registration_status'];
            case 'unit_registration_count':
                if (preg_match('/([A-Z]{3,4}[0-9]{4})/i', $input, $matches)) {
                    return ['intent' => 'unit_registration_count', 'unit_code' => strtoupper($matches[1])];
                }
                return ['intent' => 'unit_registration_count'];
            case 'unit_day':
                if (preg_match('/([A-Z]{3,4}[0-9]{4})/i', $input, $matches)) {
                    return ['intent' => 'unit_day', 'unit_code' => strtoupper($matches[1])];
                }
                return ['intent' => 'unit_day'];
            case 'view_all':
                $year_patterns = ['first' => 'First Year', 'second' => 'Second Year', 'third' => 'Third Year', 'fourth' => 'Fourth Year', '1st' => 'First Year', '2nd' => 'Second Year', '3rd' => 'Third Year', '4th' => 'Fourth Year'];
                foreach ($year_patterns as $pattern => $year) {
                    if (strpos($input, $pattern) !== false) {
                        return ['intent' => 'view_all', 'year' => $year];
                    }
                }
                return ['intent' => 'view_all'];
        }
    }
    
    $what_to_register_keywords = ['what to register', 'which units', 'units to register', 'required units', 'what units do i need', 'register this semester', 'what am i supposed to register', 'what units should i register'];
    foreach ($what_to_register_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'what_to_register'];
        }
    }
    
    $status_keywords = ['registration status', 'have i registered', 'check my registration', 'what have i registered for', 'am i fully registered', 'missing units'];
    foreach ($status_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'registration_status'];
        }
    }
    
    $timetable_keywords = ['timetable', 'schedule', 'class schedule', 'my classes', 'lecture schedule', 'time table', 'show me my timetable', 'my timetable', 'my class schedule', 'show timetable', 'show schedule', 'my schedule', 'class timetable'];
    foreach ($timetable_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'timetable'];
        }
    }
    
    // Check for download timetable queries
    $download_keywords = ['download timetable', 'download my timetable', 'get timetable pdf', 'save timetable', 'export timetable', 'pdf timetable', 'download schedule'];
    foreach ($download_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'download_timetable'];
        }
    }
    
    $advice_keywords = ['what courses should i take', 'which units should i register', 'advice on courses', 'recommend units', 'suggest courses', 'help me choose units'];
    foreach ($advice_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'course_advice'];
        }
    }
    
    $reg_keywords = ['how to register', 'register for units', 'registration process', 'how do i register', 'enroll', 'add units'];
    foreach ($reg_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'reg_help'];
        }
    }
    
    $year_patterns = [
        '/first\s*year|1st\s*year|year\s*1|freshman/i' => 'First Year',
        '/second\s*year|2nd\s*year|year\s*2|sophomore/i' => 'Second Year',
        '/third\s*year|3rd\s*year|year\s*3|junior/i' => 'Third Year',
        '/fourth\s*year|4th\s*year|year\s*4|senior/i' => 'Fourth Year'
    ];
    foreach ($year_patterns as $pattern => $year) {
        if (preg_match($pattern, strtolower($input))) {
            return ['intent' => 'view_all', 'year' => $year];
        }
    }
    
    if (preg_match('/unit|course|what is|tell me about/i', $input) && preg_match('/[A-Z]{3,4}[0-9]{4}/i', $input)) {
        return ['intent' => 'search_unit'];
    }
    
    return null;
}

// Intent detection order
$social_intent = detectSocialIntent($user_input);
if ($social_intent) {
    $intent = $social_intent;
} else {
    $priority_check = detectIntent($user_input);
    if ($priority_check) {
        $intent = $priority_check['intent'];
        if (isset($priority_check['year'])) { $GLOBALS['target_year'] = $priority_check['year']; }
        if (isset($priority_check['semester'])) { $GLOBALS['target_semester'] = $priority_check['semester']; }
        if (isset($priority_check['unit_code'])) { $GLOBALS['target_unit'] = $priority_check['unit_code']; }
    } else {
        $samples = ['hi', 'hello', 'hey', 'good morning', 'units', 'workload', 'math', 'programming', 'database', 'registration', 'register', 'enroll', 'ai', 'artificial intelligence'];
        $labels = ['greet', 'greet', 'greet', 'greet', 'view_all', 'view_all', 'search_unit', 'search_unit', 'search_unit', 'reg_help', 'reg_help', 'reg_help', 'ai_check', 'ai_check'];
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
}

/* ===============================
    10. HELPER FUNCTIONS
================================ */
function formatResponse($data, $title) {
    if (empty($data)) { return "No information found for your query."; }
    $response = "<b>$title</b><br>";
    foreach ($data as $item) { $response .= "• " . implode(" - ", $item) . "<br>"; }
    return $response;
}

function getRandomResponse($responses) {
    return $responses[array_rand($responses)];
}

/* ===============================
    11. SOCIAL RESPONSES
================================ */
$social_responses = [
    'gratitude' => ["You're very welcome! 😊 Happy to help!", "My pleasure! Is there anything else you'd like to know?", "Anytime! That's what I'm here for. 👍"],
    'apology' => ["No worries at all! How can I help you?", "It's all good! What would you like to know?", "No need to apologize! I'm here to help."],
    'how_are_you' => ["I'm doing great, thank you for asking! 😊 Ready to help you with your academic questions!", "I'm functioning perfectly! 😄 What can I do for you today?", "All systems operational and happy to chat! How about you?"],
    'compliment' => ["Aww, thank you! 😊 You're pretty awesome yourself!", "You just made my circuits happy! 🥰 How can I help you?", "Thanks! I try my best to be helpful."],
    'farewell' => ["Goodbye! 👋 Feel free to come back if you have more questions!", "Take care! Wishing you success in your studies! 🎓", "See you later! Have a great day! 😊"]
];

/* ===============================
    12. MAIN ACTION LOGIC (UPDATED)
================================ */
switch ($intent) {
    case 'greet':
        $student_name = $_SESSION['user_name'] ?? '';
        $greetings = [
            "Hey there! 👋 I'm your academic assistant. What can I help you with today?",
            "Hello! 😊 Ready to help you with your courses, timetable, or exams!",
            "Hi! Great to see you! What would you like to know about your academic journey?"
        ];
        if ($student_name) { echo "Hey $student_name! " . getRandomResponse($greetings); }
        else { echo getRandomResponse($greetings); }
        break;
    
    case 'gratitude': echo getRandomResponse($social_responses['gratitude']); break;
    case 'apology': echo getRandomResponse($social_responses['apology']); break;
    case 'how_are_you': echo getRandomResponse($social_responses['how_are_you']); break;
    case 'compliment': echo getRandomResponse($social_responses['compliment']); break;
    case 'farewell': echo getRandomResponse($social_responses['farewell']); break;
    
    /* ===============================
        WHAT TO REGISTER
    ============================== */
    case 'what_to_register':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if (!$student_reg) {
            echo "🔐 Please log in first to see the units you need to register for this semester.<br><br>";
            echo "💡 <i>Once logged in, I can tell you exactly which 6 units you should register for based on your year level!</i>";
            break;
        }
        
        $student_year = getStudentYearLevel($conn, $student_reg);
        $current_semester_num = getCurrentSemester();
        $semester_name = getSemesterName($current_semester_num);
        
        $timetable = getStudentTimetable($conn, $student_year, $current_semester_num);
        
        echo "<b>🎓 Units You Must Register For - {$student_year}, {$semester_name}</b><br><br>";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
        echo "📌 <b>You are required to register for exactly 6 units this semester.</b><br>";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
        
        if (!empty($timetable)) {
            $counter = 1;
            foreach ($timetable as $unit) {
                echo "<b>{$counter}. {$unit['unit_code']} - {$unit['course_title']}</b><br>";
                echo "   📆 <b>Day:</b> " . ($unit['day_of_week'] ?? 'TBA') . "<br>";
                echo "   ⏰ <b>Time:</b> " . ($unit['time_from'] ?? 'TBA') . " - " . ($unit['time_to'] ?? 'TBA') . "<br>";
                echo "   📍 <b>Venue:</b> " . ($unit['venue'] ?? 'TBA') . "<br>";
                echo "   👨‍🏫 <b>Lecturer:</b> " . ($unit['lecturer'] ?? 'TBA') . "<br><br>";
                $counter++;
            }
        } else {
            $required_units = getUnitsToRegister($student_year, $current_semester_num);
            $unit_titles = getUnitTitles($conn, $required_units);
            foreach ($required_units as $index => $unit_code) {
                $unit_name = $unit_titles[$unit_code] ?? 'Unit Name Not Found';
                echo "<b>" . ($index + 1) . ". {$unit_code} - {$unit_name}</b><br><br>";
            }
        }
        
        $reg_status = getStudentRegisteredUnits($conn, $student_reg, $student_year, $current_semester_num);
        if (!isset($reg_status['error'])) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
            echo "<b>📊 Your Registration Status:</b><br>";
            echo "✅ Registered: " . count($reg_status['registered']) . " / {$reg_status['total_required']}<br>";
            
            if (!empty($reg_status['not_registered_with_names'])) {
                echo "❌ Missing: ";
                $missing_codes = array_column($reg_status['not_registered_with_names'], 'code');
                echo implode(', ', $missing_codes) . "<br><br>";
                echo "<b>⚠️ Action Required:</b> Please register for the missing units immediately!<br><br>";
            } else {
                echo "🎉 Complete! You've registered for all required units!<br><br>";
            }
        }
        break;
    
    /* ===============================
        REGISTRATION STATUS (WITH NAMES)
    ============================== */
    case 'registration_status':
        $student_reg = $_SESSION['reg_number'] ?? null;
        if (!$student_reg) { echo "🔐 Please log in to check your registration status."; break; }
        
        $student_year = getStudentYearLevel($conn, $student_reg);
        $current_semester_num = getCurrentSemester();
        $semester_name = getSemesterName($current_semester_num);
        $reg_status = getStudentRegisteredUnits($conn, $student_reg, $student_year, $current_semester_num);
        
        if (isset($reg_status['error'])) { echo $reg_status['error']; break; }
        
        echo "<b>🎓 Registration Status - {$student_year}, {$semester_name}</b><br><br>";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
        
        if ($reg_status['total_registered'] == $reg_status['total_required']) {
            echo "✅ <b>Excellent!</b> You have successfully registered for ALL {$reg_status['total_required']} required units!<br><br>";
            echo "<b>📚 Your registered units:</b><br>";
            foreach ($reg_status['registered_with_names'] as $unit) {
                echo "  • <b>{$unit['code']}</b> - {$unit['name']}<br>";
            }
        } else {
            echo "⚠️ <b>Incomplete Registration!</b> You have registered for {$reg_status['total_registered']} out of {$reg_status['total_required']} required units.<br><br>";
            
            if (!empty($reg_status['registered_with_names'])) {
                echo "<b>✅ Already registered:</b><br>";
                foreach ($reg_status['registered_with_names'] as $unit) {
                    echo "  • <b>{$unit['code']}</b> - {$unit['name']}<br>";
                }
                echo "<br>";
            }
            
            if (!empty($reg_status['not_registered_with_names'])) {
                echo "<b>⚠️ Missing units (YOU MUST REGISTER THESE):</b><br>";
                foreach ($reg_status['not_registered_with_names'] as $unit) {
                    echo "  • <b style='color:#ff6600;'>{$unit['code']}</b> - {$unit['name']}<br>";
                }
                echo "<br><b>🔴 Action Required:</b> Please register for the missing units immediately!<br><br>";
            }
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
        echo "<b>📋 Complete Required Units List:</b><br>";
        foreach ($reg_status['required_with_names'] as $unit) {
            $checkmark = in_array($unit['code'], $reg_status['registered']) ? "✅" : "❌";
            echo "  {$checkmark} <b>{$unit['code']}</b> - {$unit['name']}<br>";
        }
        break;
    
    /* ===============================
        TIMETABLE (UPDATED WITH DOWNLOAD LINK)
    ============================== */
    case 'timetable':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if (!$student_reg) {
            echo "🔐 Please log in to view your personal timetable.<br><br>";
            echo "💡 <i>Once logged in, I can show you your class schedule!</i>";
            break;
        }
        
        $student_year = getStudentYearLevel($conn, $student_reg);
        $current_semester_num = getCurrentSemester();
        $semester_name = getSemesterName($current_semester_num);
        
        $timetable = getStudentTimetable($conn, $student_year, $current_semester_num);
        
        if (!empty($timetable)) {
            echo displayTimetable($timetable, $student_year, $semester_name, $student_reg);
        } else {
            $required_units = getUnitsToRegister($student_year, $current_semester_num);
            if (!empty($required_units)) {
                $unit_titles = getUnitTitles($conn, $required_units);
                echo "<b>📅 Your {$student_year} - {$semester_name} Timetable</b><br>";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
                $counter = 1;
                foreach ($required_units as $unit_code) {
                    $unit_name = $unit_titles[$unit_code] ?? 'Unit Name Not Found';
                    echo "<b>{$counter}. {$unit_code} - {$unit_name}</b><br>";
                    echo "   ⚠️ <i>Schedule details coming soon...</i><br><br>";
                    $counter++;
                }
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
                echo "💡 <i>Full timetable with days and times will be available soon.</i><br>";
            } else {
                echo "📭 No timetable found for {$student_year}, {$semester_name}.<br><br>";
                echo "💡 <i>Please contact the academic office for your class schedule.</i>";
            }
        }
        break;
    
    /* ===============================
        DOWNLOAD TIMETABLE (NEW CASE)
    ============================== */
    case 'download_timetable':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if (!$student_reg) {
            echo "🔐 Please log in to download your timetable.<br><br>";
            echo "💡 <i>Once logged in, I can provide your downloadable timetable!</i>";
            break;
        }
        
        $student_year = getStudentYearLevel($conn, $student_reg);
        $current_semester_num = getCurrentSemester();
        $semester_name = getSemesterName($current_semester_num);
        
        $timetable = getStudentTimetable($conn, $student_year, $current_semester_num);
        
        if (!empty($timetable)) {
            $download_token = base64_encode($student_reg . '_' . time());
            $download_url = "download_timetable.php?token=" . urlencode($download_token) . "&student=" . urlencode($student_reg);
            
            echo "📥 <b>Your Timetable Download Link</b><br><br>";
            echo "Click the link below to download your {$student_year} - {$semester_name} timetable:<br><br>";
            echo "🔗 <a href='{$download_url}' target='_blank' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>📄 Download Timetable (HTML/PDF)</a><br><br>";
            echo "💡 <i>The timetable will open in a new tab. You can save it as PDF using your browser's print function (Ctrl+P or Cmd+P).</i><br><br>";
            echo "📌 <b>Tip:</b> You can also say 'Show my timetable' to view it directly in the chat.";
        } else {
            echo "📭 No timetable found for {$student_year}, {$semester_name}.<br><br>";
            echo "💡 <i>Please contact the academic office for your class schedule.</i>";
        }
        break;
    
    /* ===============================
        MY COURSES (WITH NAMES)
    ============================== */
    case 'my_courses':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if ($student_reg) {
            $student_year = getStudentYearLevel($conn, $student_reg);
            $current_semester_num = getCurrentSemester();
            $semester_name = getSemesterName($current_semester_num);
            $reg_status = getStudentRegisteredUnits($conn, $student_reg, $student_year, $current_semester_num);
            
            echo "<b>🎓 Your Academic Standing - {$student_year}, {$semester_name}</b><br><br>";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
            echo "<b>📋 Required Units for This Semester (6 units):</b><br>";
            
            foreach ($reg_status['required_with_names'] as $unit) {
                $checkmark = in_array($unit['code'], $reg_status['registered']) ? "✅" : "❌";
                echo "  {$checkmark} <b>{$unit['code']}</b> - {$unit['name']}<br>";
            }
            
            echo "<br><b>Progress:</b> " . round($reg_status['completion_percentage']) . "% complete<br>";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
            
            if (!empty($reg_status['not_registered_with_names'])) {
                echo "<b>⚠️ You still need to register for these units:</b><br>";
                foreach ($reg_status['not_registered_with_names'] as $unit) {
                    echo "  • <b style='color:#ff6600;'>{$unit['code']}</b> - {$unit['name']}<br>";
                }
                echo "<br>💡 <i>Say 'What to register' to see the full timetable with days and times!</i><br><br>";
            } else {
                echo "🎉 <b>Congratulations!</b> You've registered for all required units this semester!<br><br>";
            }
        } else {
            echo "🔐 Please log in to view your courses.";
        }
        break;
    
    /* ===============================
        STUDENT INFO
    ============================== */
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
                $student_year = getStudentYearLevel($conn, $student_reg);
                echo "<b>👤 Your Profile</b><br>";
                echo "• Name: {$row['full_name']}<br>";
                echo "• Registration Number: {$row['reg_number']}<br>";
                echo "• Email: {$row['email']}<br>";
                echo "• Department: {$row['department']}<br>";
                echo "• Current Year Level: <b>{$student_year}</b><br>";
                echo "<br>💡 <i>Say 'Show my timetable' to see your class schedule!</i>";
            } else {
                echo "Hmm, I couldn't find your information. 🤔";
            }
        } else {
            echo "🔐 Please log in to view your profile information.";
        }
        break;
    
    /* ===============================
        REG HELP
    ============================== */
    case 'reg_help':
        $student_reg = $_SESSION['reg_number'] ?? null;
        if ($student_reg) {
            $student_year = getStudentYearLevel($conn, $student_reg);
            $current_semester_num = getCurrentSemester();
            $semester_name = getSemesterName($current_semester_num);
            $required_units = getUnitsToRegister($student_year, $current_semester_num);
            $reg_status = getStudentRegisteredUnits($conn, $student_reg, $student_year, $current_semester_num);
            
            echo "<b>🎓 Course Registration Guide - {$student_year}, {$semester_name}</b><br><br>";
            echo "<b>📋 You must register for exactly 6 units this semester:</b><br>";
            foreach ($reg_status['required_with_names'] as $unit) {
                $status = in_array($unit['code'], $reg_status['registered']) ? "✅ Registered" : "⚪ Not Registered";
                echo "  • <b>{$unit['code']}</b> - {$unit['name']} ({$status})<br>";
            }
            echo "<br>";
            
            if (!empty($reg_status['not_registered_with_names'])) {
                echo "<b>⚠️ Missing Units to Register:</b><br>";
                foreach ($reg_status['not_registered_with_names'] as $unit) {
                    echo "  • <b>{$unit['code']}</b> - {$unit['name']}<br>";
                }
                echo "<br><b>📝 Steps to Register:</b><br>";
                echo "1️⃣ Go to the registration portal<br>";
                echo "2️⃣ Search for the missing unit codes above<br>";
                echo "3️⃣ Add them to your registration cart<br>";
                echo "4️⃣ Submit your registration<br><br>";
            } else {
                echo "✅ <b>Great news!</b> You've already registered for all required units!<br><br>";
            }
            echo "💡 <i>Say 'Show my timetable' to see your class schedule!</i><br>";
        } else {
            echo "<b>🎓 Course Registration Guide</b><br><br>";
            echo "To register for units, please follow these steps:<br><br>";
            echo "1️⃣ <b>Log in to the portal</b> using your registration number and password<br>";
            echo "2️⃣ <b>Say 'What to register'</b> to see the 6 units you need<br>";
            echo "3️⃣ <b>Register for those units</b> in the registration portal<br>";
            echo "4️⃣ <b>Confirm your registration</b> and check your status<br><br>";
            echo "🔐 <i>Please log in first so I can show you your personalized unit list!</i>";
        }
        break;
    
    /* ===============================
        VIEW ALL UNITS
    ============================== */
    case 'view_all':
        $target_year = $GLOBALS['target_year'] ?? null;
        if (!$target_year) {
            $all_units = mysqli_query($conn, "SELECT year_level, COUNT(*) as count FROM timetable GROUP BY year_level");
            $summary = [];
            while ($row = mysqli_fetch_assoc($all_units)) {
                $summary[] = [$row['year_level'] . ": " . $row['count'] . " units"];
            }
            echo formatResponse($summary, "📚 Units by Year Level:");
            echo "<br><br>💡 <i>Tip: Try asking 'Show me first year units' or 'What are second year courses?'</i>";
            break;
        }
        
        $stmt = $conn->prepare("SELECT unit_code, course_title, semester, day_of_week, time_from, time_to, venue 
                                FROM timetable 
                                WHERE year_level = ? 
                                ORDER BY semester, day_of_week, time_from");
        $stmt->bind_param("s", $target_year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<b>📖 {$target_year} Curriculum:</b><br><br>";
            $units_by_semester = [];
            while ($row = $result->fetch_assoc()) {
                $semester = ($row['semester'] == 1) ? '1st Semester' : '2nd Semester';
                if (!isset($units_by_semester[$semester])) { $units_by_semester[$semester] = []; }
                $units_by_semester[$semester][] = $row['unit_code'] . " - " . $row['course_title'];
            }
            foreach ($units_by_semester as $semester => $units) {
                echo "<b>📌 {$semester}:</b><br>";
                foreach ($units as $unit) { echo "  • {$unit}<br>"; }
                echo "<br>";
            }
            
            $sem1_units = getUnitsToRegister($target_year, 1);
            $sem2_units = getUnitsToRegister($target_year, 2);
            $unit_titles = getUnitTitles($conn, array_merge($sem1_units, $sem2_units));
            
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
            echo "<b>🎯 Required Units (6 per semester):</b><br><br>";
            if (!empty($sem1_units)) {
                echo "<b>📌 1st Semester Required Units:</b><br>";
                foreach ($sem1_units as $unit) {
                    $unit_name = $unit_titles[$unit] ?? 'Unit Name Not Found';
                    echo "  • {$unit} - {$unit_name}<br>";
                }
                echo "<br>";
            }
            if (!empty($sem2_units)) {
                echo "<b>📌 2nd Semester Required Units:</b><br>";
                foreach ($sem2_units as $unit) {
                    $unit_name = $unit_titles[$unit] ?? 'Unit Name Not Found';
                    echo "  • {$unit} - {$unit_name}<br>";
                }
            }
        } else {
            echo "Hmm, I couldn't find any units for {$target_year}. 🤔";
        }
        break;
    
    /* ===============================
        AI CHECK (UPDATED)
    ============================== */
    case 'ai_check':
        echo "🤖 I'm your friendly AI academic assistant! Here's what I can do:<br><br>
              ✅ <b>Show my timetable</b> - Displays your class schedule with days, times, and venues<br>
              ✅ <b>Download my timetable</b> - Get a downloadable link for your timetable<br>
              ✅ <b>What to Register</b> - Shows the exact 6 units you need for your current semester<br>
              ✅ <b>Registration Status</b> - Check which required units you've registered for<br>
              ✅ <b>My Courses</b> - View your academic standing and progress<br>
              ✅ <b>Student Info</b> - View your profile information<br>
              ✅ <b>Course Advisor</b> - Get personalized course recommendations<br>
              ✅ <b>Registration Help</b> - Guide you through the registration process<br>
              ✅ <b>View Units by Year</b> - See all units for any year level<br><br>
              🎓 <b>Try asking:</b><br>
              • 'Show my timetable' - See your complete class schedule!<br>
              • 'Download my timetable' - Get a link to download your timetable!<br>
              • 'What to register' - See your 6 required units for this semester!<br>
              • 'Registration status' - Check your registration progress!<br><br>
              What would you like to know?";
        break;
    
    case 'search_unit':
    case 'unit_day':
    case 'unit_details':
    case 'unit_registration_count':
    case 'course_advice':
    case 'exam_info':
        echo "I'm here to help with your academic journey! 😊<br><br>
              Try these commands instead:<br>
              • <b>'Show my timetable'</b> - See your class schedule<br>
              • <b>'Download my timetable'</b> - Get a downloadable link<br>
              • <b>'What to register'</b> - See your required units<br>
              • <b>'Registration status'</b> - Check your progress<br><br>
              What would you like to do?";
        break;
    
    case 'fallback':
    default:
        $stmt_msg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'student', ?)");
        $stmt_msg->bind_param("ss", $sess_id, $user_input);
        $stmt_msg->execute();
        
        $friendly_fallbacks = [
            "Hmm, I'm not quite sure about that yet. 🤔 Try asking 'Show my timetable' to see your class schedule!",
            "That's a good question! Try saying 'Show my timetable' or 'Download my timetable'! 😊",
            "I can help with your class schedule! Try 'Show my timetable' to see your 6 required units with days and times! 💪"
        ];
        $bot_msg = getRandomResponse($friendly_fallbacks);
        echo $bot_msg;
        
        $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
        $stmt_bot->bind_param("ss", $sess_id, $bot_msg);
        $stmt_bot->execute();
        break;
}
?>