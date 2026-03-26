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
    // Clean the word
    $word = trim(strtolower(preg_replace('/[^a-zA-Z\s-]/', '', $word)));
    
    if (strlen($word) < 2) {
        return null;
    }
    
    // Try local database first (faster)
    $local_def = getLocalDefinition($word, $conn);
    if ($local_def) {
        return $local_def;
    }
    
    // If not in local DB, try API
    $api_def = getAPIDefinition($word);
    if ($api_def) {
        // Save to local database for future use
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
        if ($row['example']) {
            $output .= "<i>Example: \"{$row['example']}\"</i><br>";
        }
        if ($row['synonyms']) {
            $output .= "<b>Synonyms:</b> {$row['synonyms']}<br>";
        }
        return $output;
    }
    return null;
}

function getAPIDefinition($word) {
    // Free Dictionary API
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
            
            // Add synonyms if available
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
    // Extract plain text from HTML for storage
    $plain_definition = strip_tags($definition_html);
    
    // Parse the definition to extract parts
    $part_of_speech = 'noun';
    $definition_text = $plain_definition;
    $example = '';
    $synonyms = '';
    
    // Try to extract example
    if (preg_match('/Example: "([^"]+)"/', $plain_definition, $matches)) {
        $example = $matches[1];
        $definition_text = preg_replace('/Example: "[^"]+"/', '', $definition_text);
    }
    
    // Try to extract synonyms
    if (preg_match('/Synonyms: (.+)/', $plain_definition, $matches)) {
        $synonyms = $matches[1];
        $definition_text = preg_replace('/Synonyms: .+/', '', $definition_text);
    }
    
    // Clean up definition text
    $definition_text = trim(preg_replace('/\s+/', ' ', $definition_text));
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO vocabulary (word, part_of_speech, definition, example, synonyms) VALUES (?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE definition = VALUES(definition)");
    $stmt->bind_param("sssss", $word, $part_of_speech, $definition_text, $example, $synonyms);
    $stmt->execute();
}

function detectVocabularyIntent($input) {
    $vocab_keywords = ['meaning', 'define', 'what does', 'what is', 'definition of', 'vocabulary', 'word', 'means'];
    foreach ($vocab_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

function extractWordFromQuery($input) {
    // Remove common question phrases
    $input = preg_replace('/what (is|does|are) /i', '', $input);
    $input = preg_replace('/define|meaning of|definition of|vocabulary|word|means?/i', '', $input);
    $input = trim($input);
    
    // Get the last word or phrase
    $words = explode(' ', $input);
    if (count($words) > 1) {
        // For phrases like "artificial intelligence", keep them together
        if (strpos($input, 'artificial') !== false && strpos($input, 'intelligence') !== false) {
            return 'artificial intelligence';
        }
        if (strpos($input, 'machine') !== false && strpos($input, 'learning') !== false) {
            return 'machine learning';
        }
        // Otherwise take the last word
        return end($words);
    }
    return $input;
}

/* ============================================================
    3. PRIORITY 1: KNOWLEDGE BASE CHECK
============================================================ */
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
    4. PRIORITY 2: VOCABULARY CHECK (NEW)
================================ */
$vocabulary_check = detectVocabularyIntent($user_input);

if ($vocabulary_check) {
    $word_to_define = extractWordFromQuery($user_input);
    $definition = getWordDefinition($word_to_define, $conn);
    
    if ($definition) {
        echo $definition;
        
        // Friendly follow-up
        $followups = [
            "📖 Want me to explain another word?",
            "💡 Would you like to see how to use this word in a sentence?",
            "🎓 Need help with any other academic vocabulary?",
            "📚 I can help you build your vocabulary! Ask me about any word.",
            "🤓 Learning new words is great! What other word would you like to know?"
        ];
        echo "<br><br><i>" . getRandomResponse($followups) . "</i>";
        
        // Log the query
        $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
        $definition_short = substr($definition, 0, 500);
        $stmt_bot->bind_param("ss", $sess_id, $definition_short);
        $stmt_bot->execute();
        exit;
    } else {
        echo "🤔 I'm still learning the word '{$word_to_define}'!<br><br>";
        echo "💡 <i>Tip: Try asking about academic terms like 'algorithm', 'database', 'semester', or 'curriculum'!</i><br><br>";
        echo "📝 I've noted this word and will add it to my vocabulary soon!";
        
        // Save unknown word request
        $requested_by = $_SESSION['user_name'] ?? 'Guest';
        $stmt = $conn->prepare("INSERT INTO vocabulary_requests (word, requested_by) VALUES (?, ?)");
        $stmt->bind_param("ss", $word_to_define, $requested_by);
        $stmt->execute();
        exit;
    }
}

/* ===============================
    5. SOCIAL INTENT DETECTION
================================ */
function detectSocialIntent($input) {
    $gratitude = ['thank', 'thanks', 'thnx', 'thank you', 'appreciate', 'grateful', 'thx'];
    foreach ($gratitude as $word) {
        if (strpos($input, $word) !== false) {
            return 'gratitude';
        }
    }
    
    $apologies = ['sorry', 'apologize', 'my bad', 'apologies'];
    foreach ($apologies as $word) {
        if (strpos($input, $word) !== false) {
            return 'apology';
        }
    }
    
    $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'howdy', 'yo'];
    foreach ($greetings as $word) {
        if (strpos($input, $word) !== false) {
            return 'greet';
        }
    }
    
    $farewell = ['bye', 'goodbye', 'see you', 'later', 'ciao', 'farewell', 'talk to you later'];
    foreach ($farewell as $word) {
        if (strpos($input, $word) !== false) {
            return 'farewell';
        }
    }
    
    $how_are_you = ['how are you', 'how are you doing', 'how\'s it going', 'how are things', 'what\'s up', 'sup'];
    foreach ($how_are_you as $phrase) {
        if (strpos($input, $phrase) !== false) {
            return 'how_are_you';
        }
    }
    
    $compliments = ['you are great', 'you\'re awesome', 'good bot', 'nice', 'cool', 'amazing'];
    foreach ($compliments as $word) {
        if (strpos($input, $word) !== false) {
            return 'compliment';
        }
    }
    
    return null;
}

/* ===============================
    6. ACADEMIC INTENT DETECTION
================================ */
function detectIntent($input) {
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
    
    if (preg_match('/([1-4])\.([1-2])/', $input, $matches)) {
        $year_map = ['1' => 'First Year', '2' => 'Second Year', '3' => 'Third Year', '4' => 'Fourth Year'];
        $sem_map = ['1' => '1st Semester', '2' => '2nd Semester'];
        return ['intent' => 'view_all', 'year' => $year_map[$matches[1]], 'semester' => $sem_map[$matches[2]]];
    }
    
    if (preg_match('/unit|course|what is|tell me about/i', $input) && preg_match('/[A-Z]{3,4}[0-9]{4}/i', $input)) {
        return ['intent' => 'search_unit'];
    }
    
    $timetable_keywords = ['class', 'schedule', 'timetable', 'lecture', 'when is my class', 'what class', 'this week', 'today\'s class', 'classes for', 'my classes', 'class schedule'];
    foreach ($timetable_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'timetable'];
        }
    }
    
    $exam_keywords = ['exam', 'test', 'assessment', 'when is exam', 'exam date'];
    foreach ($exam_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'exam_info'];
        }
    }
    
    $student_keywords = ['who am i', 'my name', 'my details', 'my info', 'my registration', 'my email'];
    foreach ($student_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'student_info'];
        }
    }
    
    $courses_keywords = ['my courses', 'my units', 'registered courses', 'what am i taking', 'enrolled in'];
    foreach ($courses_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'my_courses'];
        }
    }
    
    return null;
}

// Intent detection order: Social > Vocabulary > Academic
$social_intent = detectSocialIntent($user_input);
if ($social_intent) {
    $intent = $social_intent;
} else {
    // Vocabulary already checked above
    $priority_check = detectIntent($user_input);
    if ($priority_check) {
        $intent = $priority_check['intent'];
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
}

/* ===============================
    7. HELPER FUNCTIONS
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

function getRandomResponse($responses) {
    return $responses[array_rand($responses)];
}

/* ===============================
    8. SOCIAL RESPONSES
================================ */
$social_responses = [
    'gratitude' => [
        "You're very welcome! 😊 Happy to help!",
        "My pleasure! Is there anything else you'd like to know?",
        "Anytime! That's what I'm here for. 👍",
        "Glad I could help! Let me know if you need anything else.",
        "You're welcome! Keep those questions coming! 💪",
        "No problem at all! Always happy to assist you.",
        "Thank you too for chatting with me! 😊"
    ],
    'apology' => [
        "No worries at all! How can I help you?",
        "It's all good! What would you like to know?",
        "No need to apologize! I'm here to help.",
        "Don't worry about it! How can I assist you?",
        "Apology not needed! Let's focus on your questions. 😊"
    ],
    'how_are_you' => [
        "I'm doing great, thank you for asking! 😊 Ready to help you with your academic questions!",
        "I'm functioning perfectly! 😄 What can I do for you today?",
        "All systems operational and happy to chat! How about you?",
        "I'm excellent! Thanks for checking in. How can I help you with your studies?",
        "Doing well! Always happy when I get to help students like you! 💪"
    ],
    'compliment' => [
        "Aww, thank you! 😊 You're pretty awesome yourself!",
        "You just made my circuits happy! 🥰 How can I help you?",
        "Thanks! I try my best to be helpful. What's your next question?",
        "That's so kind of you! 😊 Let me know what you need help with!",
        "I appreciate that! Now, how can I assist you with your academic journey?"
    ],
    'farewell' => [
        "Goodbye! 👋 Feel free to come back if you have more questions!",
        "Take care! Wishing you success in your studies! 🎓",
        "See you later! Have a great day! 😊",
        "Bye for now! Remember, I'm always here when you need help.",
        "Talk to you later! Keep working hard! 💪"
    ]
];

/* ===============================
    9. MAIN ACTION LOGIC
================================ */
switch ($intent) {
    case 'greet':
        $student_name = $_SESSION['user_name'] ?? '';
        $greetings = [
            "Hey there! 👋 I'm your academic assistant. What can I help you with today?",
            "Hello! 😊 Ready to help you with your courses, timetable, or exams!",
            "Hi! Great to see you! What would you like to know about your academic journey?",
            "Hey! I'm here to make your academic life easier. What's on your mind?",
            "Hello there! 👋 Whether it's units, schedules, or exams, I've got you covered!"
        ];
        
        if ($student_name) {
            echo "Hey $student_name! " . getRandomResponse($greetings);
        } else {
            echo getRandomResponse($greetings);
        }
        break;
    
    case 'gratitude':
        echo getRandomResponse($social_responses['gratitude']);
        break;
    
    case 'apology':
        echo getRandomResponse($social_responses['apology']);
        break;
    
    case 'how_are_you':
        echo getRandomResponse($social_responses['how_are_you']);
        break;
    
    case 'compliment':
        echo getRandomResponse($social_responses['compliment']);
        break;
    
    case 'farewell':
        echo getRandomResponse($social_responses['farewell']);
        break;

    case 'view_all':
        $target_year = $GLOBALS['target_year'] ?? null;
        $target_semester = $GLOBALS['target_semester'] ?? null;
        
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
            $all_units = mysqli_query($conn, "SELECT year_level, COUNT(*) as count FROM academic_workload GROUP BY year_level");
            $summary = [];
            while ($row = mysqli_fetch_assoc($all_units)) {
                $summary[] = [$row['year_level'] . ": " . $row['count'] . " units"];
            }
            echo formatResponse($summary, "📚 Units by Year Level:");
            echo "<br><br>💡 <i>Tip: Try asking 'Show me first year units' or 'What are second year courses?'</i>";
            break;
        }
        
        if ($target_semester) {
            $stmt = $conn->prepare("SELECT unit_code, unit_name, semester_level, offering_time 
                                    FROM academic_workload 
                                    WHERE year_level = ? AND semester_level = ? 
                                    ORDER BY unit_code");
            $stmt->bind_param("ss", $target_year, $target_semester);
        } else {
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
            
            echo "<b>📖 $target_year Curriculum:</b><br>";
            foreach ($units_by_semester as $semester => $units) {
                echo "<br><b>📌 $semester:</b><br>";
                foreach ($units as $unit) {
                    echo "• $unit<br>";
                }
            }
            
            $followups = [
                "Need details about any specific unit? Just ask me! 😊",
                "Want to know when these units are offered? I can help with that!",
                "Interested in registering for any of these? Let me know!"
            ];
            echo "<br><i>" . getRandomResponse($followups) . "</i>";
        } else {
            echo "Hmm, I couldn't find any units for $target_year. 🤔 Let me check with the academic office for you!";
        }
        break;

    case 'timetable':
        $student_reg = $_SESSION['reg_number'] ?? null;
        
        if ($student_reg) {
            $current_semester = '1';
            $current_year = date('Y');
            
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
                echo "<b>📅 Your Class Schedule</b><br><br>";
                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<b>{$counter}. {$row['unit_code']} - {$row['course_title']}</b><br>";
                    echo "   ⏰ Time: {$row['time_from']} - {$row['time_to']}<br>";
                    echo "   📍 Venue: {$row['venue']}<br>";
                    echo "   👨‍🏫 Lecturer: {$row['lecturer']}<br><br>";
                    $counter++;
                }
                echo "🎓 <i>Don't forget to check for any updates! Classes might be subject to change.</i>";
            } else {
                echo "📭 You don't have any classes scheduled yet. <br><br>💡 <i>Make sure you're registered for courses. Need help with registration?</i>";
            }
        } else {
            echo "🔐 Please log in to view your personal timetable. <br><br>💡 <i>Once logged in, I can show you your exact class schedule!</i>";
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
                echo "<b>📚 Your Registered Courses</b><br><br>";
                $current_year = '';
                while ($row = $result->fetch_assoc()) {
                    if ($current_year != $row['academic_year']) {
                        if ($current_year != '') echo "<br>";
                        $current_year = $row['academic_year'];
                        echo "<b>Academic Year: {$current_year}</b><br>";
                    }
                    echo "• {$row['unit_code']} - {$row['unit_name']} <br>";
                    echo "  <span style='font-size:0.9em; color:#666;'>{$row['year_level']}, {$row['semester_level']} • Status: {$row['status']}</span><br>";
                }
                echo "<br>💡 <i>Need to register for more units? Let me know!</i>";
            } else {
                echo "📭 You haven't registered for any courses yet. <br><br>💡 <i>Would you like help with the registration process?</i>";
            }
        } else {
            echo "🔐 Please log in to view your registered courses.";
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
                echo "<b>👤 Your Profile</b><br>";
                echo "• Name: {$row['full_name']}<br>";
                echo "• Registration Number: {$row['reg_number']}<br>";
                echo "• Email: {$row['email']}<br>";
                echo "• Department: {$row['department']}<br>";
                echo "<br>💡 <i>Everything look correct? Let me know if you need to update anything!</i>";
            } else {
                echo "Hmm, I couldn't find your information. 🤔 Please contact the academic office.";
            }
        } else {
            echo "🔐 Please log in to view your profile information.";
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
                echo "<b>📝 Your Upcoming Exams</b><br><br>";
                while ($row = $result->fetch_assoc()) {
                    $exam_date = ($row['exam_date'] != '0000-00-00') ? date('F j, Y', strtotime($row['exam_date'])) : 'Date TBA';
                    echo "• <b>{$row['unit_code']} - {$row['course_title']}</b><br>";
                    echo "  📅 Date: $exam_date<br>";
                    echo "  📍 Venue: " . ($row['venue'] ?: 'TBA') . "<br><br>";
                }
                echo "🎯 <i>Good luck with your exams! Study smart! 💪</i>";
            } else {
                echo "📅 No exam dates have been scheduled yet. <br><br>💡 <i>I'll let you know as soon as they're announced!</i>";
            }
        } else {
            echo "🔐 Please log in to view your exam schedule.";
        }
        break;

    case 'search_unit':
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
            echo "<b>🔍 Found matching units:</b><br><br>";
            while ($row = $result->fetch_assoc()) {
                echo "• <b>{$row['unit_code']}</b> - {$row['unit_name']}<br>";
                echo "  📚 {$row['year_level']}, {$row['semester_level']}<br>";
                echo "  🕒 Offered: {$row['offering_time']}<br><br>";
            }
        } else {
            echo "🔍 Hmm, I couldn't find '$search_term' in our system. 🤔<br><br>";
            echo "💡 <i>Try checking the unit code or name. Need help finding a specific course?</i>";
        }
        break;

    case 'reg_help':
        $responses = [
            "📝 To register for courses:<br>
             1️⃣ Go to the registration section in your portal<br>
             2️⃣ Select the units for your year level<br>
             3️⃣ Confirm your registration<br><br>
             Need help with specific units? Just ask me about them! 😊",
            
            "🎓 Registration help? I've got you!<br>
             • First, check your year level requirements<br>
             • Pick your units from the academic workload<br>
             • Submit before the deadline!<br><br>
             Want me to show you available units for your year?"
        ];
        echo getRandomResponse($responses);
        break;

    case 'ai_check':
        echo "🤖 I'm your friendly AI academic assistant! Here's what I can do:<br><br>
              ✅ Show units by year level (e.g., 'Show me second year units')<br>
              ✅ Search for specific units by code or name<br>
              ✅ Display your timetable (login required)<br>
              ✅ Show your exam schedule (login required)<br>
              ✅ List your registered courses (login required)<br>
              ✅ Help with registration questions<br>
              ✅ Define words and build vocabulary (e.g., 'What does algorithm mean?')<br><br>
              💬 And I love small talk too! Ask me how I'm doing! 😊<br><br>
              What would you like to know?";
        break;

    case 'fallback':
    default:
        fallback_logic:
        $stmt_msg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'student', ?)");
        $stmt_msg->bind_param("ss", $sess_id, $user_input);
        $stmt_msg->execute();

        $student_name = $_SESSION['user_name'] ?? 'Guest Student';
        
        $friendly_fallbacks = [
            "Hmm, I'm not quite sure about that yet. 🤔 Let me ask the admin to help you with that! I've forwarded your question to them.",
            "That's a good question! I'm still learning, so I'll pass this to the admin team. They'll get back to you soon! 😊",
            "I wish I could answer that, but I need some help from the admin. I've sent your question their way!",
            "Interesting question! I've escalated this to the admin for you. They'll help you out soon! 💪"
        ];
        
        $check_ref = $conn->prepare("SELECT id FROM admin_referrals WHERE conversation_id = ? AND status = 'pending'");
        $check_ref->bind_param("s", $sess_id);
        $check_ref->execute();
        
        if ($check_ref->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO admin_referrals (sender_name, status, conversation_id) VALUES (?, 'pending', ?)");
            $stmt->bind_param("ss", $student_name, $sess_id);
            $stmt->execute();
            $bot_msg = getRandomResponse($friendly_fallbacks);
        } else {
            $bot_msg = "I've already sent your previous question to the admin. They'll respond as soon as they can! 😊 Anything else I can help with in the meantime?";
        }

        echo $bot_msg;
        
        $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
        $stmt_bot->bind_param("ss", $sess_id, $bot_msg);
        $stmt_bot->execute();
        break; 
}
?>