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
    3. FUZZY MATCHING FUNCTIONS
============================================================ */
function fuzzyMatch($input, $target, $threshold = 70) {
    // Convert to lowercase for comparison
    $input = strtolower(trim($input));
    $target = strtolower(trim($target));
    
    // Exact match
    if ($input === $target) {
        return true;
    }
    
    // Calculate similarity percentage
    similar_text($input, $target, $percent);
    
    // Levenshtein distance (lower is better)
    $distance = levenshtein($input, $target);
    $max_len = max(strlen($input), strlen($target));
    $lev_percent = ($max_len > 0) ? (1 - ($distance / $max_len)) * 100 : 0;
    
    // Use the better of the two percentages
    $similarity = max($percent, $lev_percent);
    
    return $similarity >= $threshold;
}

function getClosestMatch($input, $options, $threshold = 70) {
    $best_match = null;
    $best_score = 0;
    
    foreach ($options as $option) {
        $input = strtolower(trim($input));
        $option = strtolower(trim($option));
        
        // Calculate similarity
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
    // Get all unit codes and names
    $query = "SELECT unit_code, unit_name FROM academic_workload";
    $result = mysqli_query($conn, $query);
    
    $matches = [];
    $search = strtolower(trim($search_term));
    
    while ($row = mysqli_fetch_assoc($result)) {
        $unit_code = strtolower($row['unit_code']);
        $unit_name = strtolower($row['unit_name']);
        
        // Calculate similarity for code
        similar_text($search, $unit_code, $code_similarity);
        $code_distance = levenshtein($search, $unit_code);
        $code_max_len = max(strlen($search), strlen($unit_code));
        $code_lev_percent = ($code_max_len > 0) ? (1 - ($code_distance / $code_max_len)) * 100 : 0;
        $code_score = max($code_similarity, $code_lev_percent);
        
        // Calculate similarity for name
        similar_text($search, $unit_name, $name_similarity);
        $name_distance = levenshtein($search, $unit_name);
        $name_max_len = max(strlen($search), strlen($unit_name));
        $name_lev_percent = ($name_max_len > 0) ? (1 - ($name_distance / $name_max_len)) * 100 : 0;
        $name_score = max($name_similarity, $name_lev_percent);
        
        // Use the best score (prioritize code matches slightly)
        $score = max($code_score, $name_score);
        
        if ($score >= 60) { // 60% similarity threshold
            $matches[] = [
                'unit_code' => $row['unit_code'],
                'unit_name' => $row['unit_name'],
                'score' => $score,
                'matched_on' => ($code_score > $name_score) ? 'code' : 'name'
            ];
        }
    }
    
    // Sort by score (highest first)
    usort($matches, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    // Return top matches
    return array_slice($matches, 0, $limit);
}

function fuzzyDetectIntent($input) {
    // Common intent phrases with variations
    $intent_patterns = [
        'greet' => ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'howdy', 'sup', 'yo', 'hie', 'helo', 'helloo', 'morning'],
        'farewell' => ['bye', 'goodbye', 'see you', 'later', 'ciao', 'farewell', 'talk later', 'by', 'bey', 'seeya'],
        'gratitude' => ['thank', 'thanks', 'appreciate', 'grateful', 'thx', 'thanx', 'thankyou', 'thnx'],
        'how_are_you' => ['how are you', 'how do you do', 'how are you doing', 'how is it going', 'whats up', 'how r u', 'howre you', 'how you doing'],
        'my_courses' => ['my courses', 'my units', 'registered courses', 'what am i taking', 'enrolled in', 'my classes', 'my subjects', 'my reg courses', 'my registred courses'],
        'reg_help' => ['how to register', 'register for units', 'registration process', 'how do i register', 'register courses', 'enroll', 'add units', 'sign up', 'registration help', 'how to enroll', 'how to add units'],
        'view_all' => ['show me', 'view units', 'list units', 'units for', 'courses for', 'subjects for', 'first year', 'second year', 'third year', 'fourth year'],
        'timetable' => ['timetable', 'schedule', 'class schedule', 'my classes', 'lecture schedule', 'time table', 'class time', 'when is class'],
        'exam_info' => ['exam', 'test', 'assessment', 'exam schedule', 'exam date', 'when is exam', 'exam time'],
        'student_info' => ['who am i', 'my details', 'my info', 'my profile', 'my registration number', 'my email', 'student details'],
        'search_unit' => ['search unit', 'find unit', 'look up', 'unit details', 'course details', 'about unit', 'tell me about'],
        'ai_check' => ['what can you do', 'help', 'capabilities', 'what do you do', 'features', 'how can you help', 'what you can do'],
        'unit_day' => ['when is', 'what day', 'what time', 'schedule for', 'class for', 'taught on', 'day is', 'time is'],
        'course_advice' => ['what courses should i take', 'which units should i register', 'advice on courses', 'recommend units', 'suggest courses', 'what to register', 'courses to take', 'units to take', 'registration advice', 'course recommendation', 'what units should i do', 'which units to choose', 'help me choose units']
    ];
    
    $input_lower = strtolower(trim($input));
    $best_intent = null;
    $best_score = 0;
    
    foreach ($intent_patterns as $intent => $patterns) {
        foreach ($patterns as $pattern) {
            // Check if pattern is contained in input
            if (strpos($input_lower, $pattern) !== false) {
                return $intent;
            }
            
            // For longer patterns, try fuzzy matching
            if (strlen($pattern) > 5) {
                similar_text($input_lower, $pattern, $similarity);
                if ($similarity > $best_score && $similarity > 70) {
                    $best_score = $similarity;
                    $best_intent = $intent;
                }
            }
        }
    }
    
    // Try word-by-word matching for typos
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

/* ============================================================
    4. PRIORITY 1: KNOWLEDGE BASE CHECK
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
    5. PRIORITY 2: VOCABULARY CHECK
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
            "📚 I can help you build your vocabulary! Ask me about any word.",
            "🤓 Learning new words is great! What other word would you like to know?"
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
    6. SOCIAL INTENT DETECTION
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
    7. ACADEMIC INTENT DETECTION (ENHANCED WITH FUZZY)
================================ */
function detectIntent($input) {
    // First, try fuzzy intent detection
    $fuzzy_intent = fuzzyDetectIntent($input);
    if ($fuzzy_intent) {
        switch ($fuzzy_intent) {
            case 'reg_help':
                return ['intent' => 'reg_help'];
            case 'my_courses':
                return ['intent' => 'my_courses'];
            case 'timetable':
                return ['intent' => 'timetable'];
            case 'exam_info':
                return ['intent' => 'exam_info'];
            case 'student_info':
                return ['intent' => 'student_info'];
            case 'ai_check':
                return ['intent' => 'ai_check'];
            case 'greet':
                return ['intent' => 'greet'];
            case 'farewell':
                return ['intent' => 'farewell'];
            case 'gratitude':
                return ['intent' => 'gratitude'];
            case 'how_are_you':
                return ['intent' => 'how_are_you'];
            case 'course_advice':
                return ['intent' => 'course_advice'];
            case 'unit_day':
                // Extract unit code from query
                if (preg_match('/([A-Z]{3,4}[0-9]{4})/i', $input, $matches)) {
                    return ['intent' => 'unit_day', 'unit_code' => strtoupper($matches[1])];
                }
                return ['intent' => 'unit_day'];
            case 'view_all':
                // Extract year from input
                $year_patterns = [
                    'first' => 'First Year',
                    'second' => 'Second Year',
                    'third' => 'Third Year',
                    'fourth' => 'Fourth Year',
                    '1st' => 'First Year',
                    '2nd' => 'Second Year',
                    '3rd' => 'Third Year',
                    '4th' => 'Fourth Year'
                ];
                foreach ($year_patterns as $pattern => $year) {
                    if (strpos($input, $pattern) !== false) {
                        return ['intent' => 'view_all', 'year' => $year];
                    }
                }
                return ['intent' => 'view_all'];
        }
    }
    
    // Check for unit day/time queries
    if (preg_match('/(when|what day|what time|schedule for|class for|taught on)\s+(is\s+)?([A-Z]{3,4}[0-9]{4})/i', $input, $matches)) {
        return ['intent' => 'unit_day', 'unit_code' => strtoupper($matches[3])];
    }
    
    // Check for unit details/prerequisites
    if (preg_match('/(details?|about|tell me about|info|prerequisites?|requirements?)/i', $input) && preg_match('/[A-Z]{3,4}[0-9]{4}/i', $input, $matches)) {
        return ['intent' => 'unit_details', 'unit_code' => strtoupper($matches[0])];
    }
    
    // Check for course advice/recommendation queries
    $advice_keywords = ['what courses should i take', 'which units should i register', 'advice on courses', 'recommend units', 'suggest courses', 'what to register', 'courses to take', 'units to take', 'registration advice', 'course recommendation', 'what units should i do', 'which units to choose', 'help me choose units'];
    foreach ($advice_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'course_advice'];
        }
    }
    
    // Check for registration help FIRST (priority over view_all)
    $reg_keywords = ['how to register', 'register for units', 'registration process', 'how do i register', 'register courses', 'enroll', 'add units', 'sign up for units'];
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
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'timetable'];
        }
    }
    
    $exam_keywords = ['exam', 'test', 'assessment', 'when is exam', 'exam date'];
    foreach ($exam_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
            return ['intent' => 'exam_info'];
        }
    }
    
    $student_keywords = ['who am i', 'my name', 'my details', 'my info', 'my registration', 'my email'];
    foreach ($student_keywords as $keyword) {
        if (strpos($input, $keyword) !== false) {
            return ['intent' => 'student_info'];
        }
    }
    
    $courses_keywords = ['my courses', 'my units', 'registered courses', 'what am i taking', 'enrolled in', 'all units', 'available units', 'all courses'];
    foreach ($courses_keywords as $keyword) {
        if (strpos($input, $keyword) !== false || fuzzyMatch($input, $keyword, 70)) {
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
        if (isset($priority_check['unit_code'])) {
            $GLOBALS['target_unit'] = $priority_check['unit_code'];
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
    8. HELPER FUNCTIONS
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
    9. SOCIAL RESPONSES
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
    10. MAIN ACTION LOGIC
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
    
    case 'course_advice':
        $student_reg = $_SESSION['reg_number'] ?? null;
        $student_name = $_SESSION['user_name'] ?? 'Student';
        
        if (!$student_reg) {
            echo "<b>🎓 Course Registration Advice</b><br><br>";
            echo "Hi there! To give you personalized course recommendations, please log in first.<br><br>";
            echo "Once logged in, I can:<br>";
            echo "• Analyze your current registered courses<br>";
            echo "• Recommend units for your current semester<br>";
            echo "• Suggest units to prepare for next semester<br>";
            echo "• Show you the complete curriculum structure<br><br>";
            echo "🔐 <i>Please log in to get personalized course advice!</i>";
            break;
        }
        
        // Determine current semester and year
        $current_month = date('n');
        $current_year = date('Y');
        
        // Determine current semester (1st Semester: Jan-June, 2nd Semester: July-Dec)
        if ($current_month >= 1 && $current_month <= 6) {
            $current_semester = '1st Semester';
            $next_semester = '2nd Semester';
            $current_semester_num = 1;
            $next_semester_num = 2;
        } else {
            $current_semester = '2nd Semester';
            $next_semester = '1st Semester';
            $current_semester_num = 2;
            $next_semester_num = 1;
        }
        
        // Get student's current year level based on registered courses
        $year_query = "SELECT DISTINCT aw.year_level 
                      FROM registered_courses rc 
                      JOIN academic_workload aw ON rc.unit_code = aw.unit_code 
                      WHERE rc.student_reg_no = ? 
                      LIMIT 1";
        $year_stmt = $conn->prepare($year_query);
        $year_stmt->bind_param("s", $student_reg);
        $year_stmt->execute();
        $year_result = $year_stmt->get_result();
        
        $current_year_level = 'First Year'; // Default
        if ($year_result->num_rows > 0) {
            $current_year_level = $year_result->fetch_assoc()['year_level'];
        }
        
        // Get already registered units
        $registered_query = "SELECT unit_code FROM registered_courses WHERE student_reg_no = ?";
        $registered_stmt = $conn->prepare($registered_query);
        $registered_stmt->bind_param("s", $student_reg);
        $registered_stmt->execute();
        $registered_result = $registered_stmt->get_result();
        $registered_units = [];
        while ($row = $registered_result->fetch_assoc()) {
            $registered_units[] = $row['unit_code'];
        }
        
        echo "<b>🎓 Academic Course Advisor</b><br><br>";
        echo "Hi $student_name! Let me help you plan your academic journey. 📚<br><br>";
        
        // ========== CURRENT SEMESTER ADVICE ==========
        echo "<b>📌 Current Semester: {$current_semester} ({$current_year})</b><br><br>";
        
        // Get units available for current semester in current year level
        $current_units_query = "SELECT unit_code, unit_name, semester_level, offering_time 
                               FROM academic_workload 
                               WHERE year_level = ? AND semester_level = ? 
                               ORDER BY unit_code";
        $current_stmt = $conn->prepare($current_units_query);
        $current_stmt->bind_param("ss", $current_year_level, $current_semester);
        $current_stmt->execute();
        $current_units_result = $current_stmt->get_result();
        
        $available_current = [];
        $already_taken_current = [];
        
        if ($current_units_result->num_rows > 0) {
            while ($row = $current_units_result->fetch_assoc()) {
                if (in_array($row['unit_code'], $registered_units)) {
                    $already_taken_current[] = $row;
                } else {
                    $available_current[] = $row;
                }
            }
            
            if (!empty($already_taken_current)) {
                echo "✅ <b>Units you're already registered for this semester:</b><br>";
                foreach ($already_taken_current as $unit) {
                    echo "  • <b>{$unit['unit_code']}</b> - {$unit['unit_name']}<br>";
                }
                echo "<br>";
            }
            
            if (!empty($available_current)) {
                echo "📚 <b>Recommended units to register for this semester:</b><br>";
                foreach ($available_current as $unit) {
                    echo "  • <b>{$unit['unit_code']}</b> - {$unit['unit_name']}<br>";
                    echo "    <span style='font-size:0.85em; color:#666;'>Offered: {$unit['offering_time']}</span><br>";
                }
                echo "<br>";
                
                if (count($available_current) > 0) {
                    echo "💡 <i>You should register for these units to stay on track with your curriculum.</i><br><br>";
                }
            } else {
                echo "✅ Great! You're already registered for all required units this semester!<br><br>";
            }
        } else {
            echo "⚠️ No units found for {$current_year_level} in {$current_semester}.<br><br>";
        }
        
        // ========== NEXT SEMESTER ADVICE ==========
        // Determine next year level (if next semester is 1st semester, year increases)
        $next_year_level = $current_year_level;
        if ($next_semester == '1st Semester') {
            // Moving to next year
            $year_mapping = [
                'First Year' => 'Second Year',
                'Second Year' => 'Third Year',
                'Third Year' => 'Fourth Year',
                'Fourth Year' => 'Fourth Year'
            ];
            $next_year_level = $year_mapping[$current_year_level] ?? $current_year_level;
        }
        
        echo "<b>🔮 Next Semester: {$next_semester} ({$current_year})</b><br><br>";
        
        // Get units for next semester
        $next_units_query = "SELECT unit_code, unit_name, semester_level, offering_time 
                            FROM academic_workload 
                            WHERE year_level = ? AND semester_level = ? 
                            ORDER BY unit_code";
        $next_stmt = $conn->prepare($next_units_query);
        $next_stmt->bind_param("ss", $next_year_level, $next_semester);
        $next_stmt->execute();
        $next_units_result = $next_stmt->get_result();
        
        if ($next_units_result->num_rows > 0) {
            echo "📚 <b>Units that will be offered next semester:</b><br>";
            while ($row = $next_units_result->fetch_assoc()) {
                echo "  • <b>{$row['unit_code']}</b> - {$row['unit_name']}<br>";
                echo "    <span style='font-size:0.85em; color:#666;'>Offered: {$row['offering_time']}</span><br>";
            }
            echo "<br>";
            echo "💡 <i>Prepare to register for these units when the registration period opens!</i><br><br>";
        } else {
            echo "⚠️ Next semester's units for {$next_year_level} are not yet available.<br><br>";
        }
        
        // ========== COMPLETE CURRICULUM OVERVIEW ==========
        echo "<b>📖 Complete {$current_year_level} Curriculum Overview</b><br><br>";
        
        $all_units_query = "SELECT unit_code, unit_name, semester_level, offering_time 
                           FROM academic_workload 
                           WHERE year_level = ? 
                           ORDER BY semester_level, unit_code";
        $all_stmt = $conn->prepare($all_units_query);
        $all_stmt->bind_param("s", $current_year_level);
        $all_stmt->execute();
        $all_units_result = $all_stmt->get_result();
        
        if ($all_units_result->num_rows > 0) {
            $units_by_semester = [];
            while ($row = $all_units_result->fetch_assoc()) {
                $semester = $row['semester_level'];
                if (!isset($units_by_semester[$semester])) {
                    $units_by_semester[$semester] = [];
                }
                $units_by_semester[$semester][] = $row;
            }
            
            foreach ($units_by_semester as $semester => $units) {
                echo "<b>📌 {$semester}:</b><br>";
                foreach ($units as $unit) {
                    $status = in_array($unit['unit_code'], $registered_units) ? "✅ Registered" : "⚪ Pending";
                    echo "  • <b>{$unit['unit_code']}</b> - {$unit['unit_name']}<br>";
                    echo "    <span style='font-size:0.85em; color:#666;'>Status: {$status} • Offered: {$unit['offering_time']}</span><br>";
                }
                echo "<br>";
            }
        }
        
        // ========== PROGRESS SUMMARY ==========
        $total_units_for_year = $all_units_result->num_rows;
        $registered_count = count(array_intersect($registered_units, array_column($units_by_semester[$current_semester] ?? [], 'unit_code')));
        
        echo "<b>📊 Your Progress Summary</b><br>";
        echo "• Total units for {$current_year_level}: {$total_units_for_year}<br>";
        echo "• Units registered this semester: {$registered_count}/" . (count($units_by_semester[$current_semester] ?? [])) . "<br>";
        
        $completion_percentage = ($total_units_for_year > 0) ? round(($registered_count / $total_units_for_year) * 100) : 0;
        echo "• Overall completion for this year: {$completion_percentage}%<br><br>";
        
        if ($completion_percentage < 50 && count($available_current) > 0) {
            echo "⚠️ <b>Action Required:</b> You still need to register for " . count($available_current) . " unit(s) this semester. Don't miss the registration deadline!<br><br>";
        } elseif ($completion_percentage >= 50 && $completion_percentage < 100) {
            echo "👍 <b>Good progress!</b> You're on track. Remember to register for the remaining units before the deadline.<br><br>";
        } elseif ($completion_percentage == 100 && $total_units_for_year > 0) {
            echo "🎉 <b>Excellent!</b> You've completed all units for {$current_year_level}! You're ready to move to the next year level.<br><br>";
        }
        
        // ========== RECOMMENDATIONS ==========
        echo "<b>💡 Personalized Recommendations:</b><br>";
        
        if (count($available_current) > 0) {
            echo "• <b>Priority:</b> Register for the " . count($available_current) . " unit(s) listed above for this semester<br>";
        }
        
        if ($next_units_result->num_rows > 0) {
            echo "• <b>Plan ahead:</b> Next semester, you'll need to take " . $next_units_result->num_rows . " unit(s). Start preparing now!<br>";
        }
        
        echo "• <b>Stay organized:</b> Keep track of registration deadlines and exam dates<br>";
        echo "• <b>Need help?</b> Ask me about any specific unit or check your timetable<br><br>";
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
        echo "❓ <b>What would you like to do next?</b><br>";
        echo "• Ask about a specific unit (e.g., 'Tell me about BIT3208')<br>";
        echo "• View your timetable ('Show my timetable')<br>";
        echo "• Check exam dates ('When are my exams?')<br>";
        echo "• Get registration help ('How to register?')<br>";
        
        break;
    
    case 'unit_day':
        $unit_code = $GLOBALS['target_unit'] ?? '';
        
        // If no unit code was captured, try to extract it from input
        if (!$unit_code && preg_match('/([A-Z]{3,4}[0-9]{4})/i', $user_input, $matches)) {
            $unit_code = strtoupper($matches[1]);
        }
        
        if ($unit_code) {
            $current_semester = '1';
            $current_year = date('Y');
            
            // Get timetable info for the specific unit
            $query = "SELECT day_of_week, time_from, time_to, venue, lecturer, course_title 
                     FROM timetable 
                     WHERE unit_code = ? AND semester = ? AND academic_year = ? 
                     LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $unit_code, $current_semester, $current_year);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<b>📚 {$unit_code} - {$row['course_title']}</b><br><br>";
                if (!empty($row['day_of_week']) && $row['day_of_week'] != '0000-00-00') {
                    echo "📆 <b>Day:</b> {$row['day_of_week']}<br>";
                    echo "⏰ <b>Time:</b> {$row['time_from']} - {$row['time_to']}<br>";
                    echo "📍 <b>Venue:</b> {$row['venue']}<br>";
                    echo "👨‍🏫 <b>Lecturer:</b> {$row['lecturer']}<br><br>";
                    echo "💡 <i>Would you like to know about any other unit's schedule?</i>";
                } else {
                    echo "❌ The schedule for {$unit_code} hasn't been released yet.<br><br>";
                    echo "💡 <i>Please check back later or contact the academic office for more information.</i>";
                }
            } else {
                echo "❌ I couldn't find the schedule for {$unit_code}.<br><br>";
                echo "💡 <i>Possible reasons:</i><br>";
                echo "• The unit code might be incorrect<br>";
                echo "• You may not be registered for this unit<br>";
                echo "• The timetable for this semester hasn't been released yet<br><br>";
                echo "Try asking: 'Show me my timetable' to see all your classes, or 'What units are available?'";
            }
        } else {
            echo "❓ Please specify which unit you want to know about.<br><br>";
            echo "For example:<br>";
            echo "• 'When is BIT3208 taught?'<br>";
            echo "• 'What day is BBM1101?'<br>";
            echo "• 'Schedule for BAF1101'<br><br>";
            echo "Or you can ask 'Show me my timetable' to see all your classes at once!";
        }
        break;

    case 'unit_details':
        $unit_code = $GLOBALS['target_unit'] ?? '';
        
        if ($unit_code) {
            // Get unit details
            $unit_query = "SELECT * FROM academic_workload WHERE unit_code = ?";
            $unit_stmt = $conn->prepare($unit_query);
            $unit_stmt->bind_param("s", $unit_code);
            $unit_stmt->execute();
            $unit_result = $unit_stmt->get_result();
            
            if ($unit_result->num_rows > 0) {
                $unit = $unit_result->fetch_assoc();
                
                echo "<b>📖 Unit Details: {$unit['unit_code']} - {$unit['unit_name']}</b><br><br>";
                echo "<b>📚 Year Level:</b> {$unit['year_level']}<br>";
                echo "<b>📅 Semester:</b> {$unit['semester_level']}<br>";
                echo "<b>🕒 Offering Time:</b> {$unit['offering_time']}<br>";
                
                // Get timetable info to show day and time if available
                $current_semester = '1';
                $current_year = date('Y');
                $timetable_query = "SELECT day_of_week, time_from, time_to, venue, lecturer 
                                   FROM timetable 
                                   WHERE unit_code = ? AND semester = ? AND academic_year = ? 
                                   LIMIT 1";
                $timetable_stmt = $conn->prepare($timetable_query);
                $timetable_stmt->bind_param("sss", $unit_code, $current_semester, $current_year);
                $timetable_stmt->execute();
                $timetable_result = $timetable_stmt->get_result();
                
                if ($timetable_result->num_rows > 0) {
                    $schedule = $timetable_result->fetch_assoc();
                    echo "<br><b>📅 Class Schedule:</b><br>";
                    echo "   📆 Day: {$schedule['day_of_week']}<br>";
                    echo "   ⏰ Time: {$schedule['time_from']} - {$schedule['time_to']}<br>";
                    echo "   📍 Venue: {$schedule['venue']}<br>";
                    if (!empty($schedule['lecturer'])) {
                        echo "   👨‍🏫 Lecturer: {$schedule['lecturer']}<br>";
                    }
                }
                
                // Get prerequisites if table exists
                $prereq_check = $conn->query("SHOW TABLES LIKE 'unit_prerequisites'");
                if ($prereq_check && $prereq_check->num_rows > 0) {
                    $prereq_query = "SELECT prerequisite_code FROM unit_prerequisites WHERE unit_code = ?";
                    $prereq_stmt = $conn->prepare($prereq_query);
                    if ($prereq_stmt) {
                        $prereq_stmt->bind_param("s", $unit_code);
                        $prereq_stmt->execute();
                        $prereq_result = $prereq_stmt->get_result();
                        
                        if ($prereq_result && $prereq_result->num_rows > 0) {
                            echo "<br><b>📋 Prerequisites:</b><br>";
                            while ($prereq = $prereq_result->fetch_assoc()) {
                                echo "  • {$prereq['prerequisite_code']}<br>";
                            }
                        } else {
                            echo "<br><b>📋 Prerequisites:</b> None required<br>";
                        }
                        $prereq_stmt->close();
                    } else {
                        echo "<br><b>📋 Prerequisites:</b> Check with academic advisor<br>";
                    }
                } else {
                    echo "<br><b>📋 Prerequisites:</b> Check with academic advisor<br>";
                }
                
                // Get lecturer info if table exists
                $lecturer_check = $conn->query("SHOW TABLES LIKE 'unit_lecturers'");
                if ($lecturer_check && $lecturer_check->num_rows > 0) {
                    $lecturer_query = "SELECT lecturer_name, email, office FROM unit_lecturers WHERE unit_code = ? LIMIT 1";
                    $lecturer_stmt = $conn->prepare($lecturer_query);
                    if ($lecturer_stmt) {
                        $lecturer_stmt->bind_param("s", $unit_code);
                        $lecturer_stmt->execute();
                        $lecturer_result = $lecturer_stmt->get_result();
                        
                        if ($lecturer_result && $lecturer_result->num_rows > 0) {
                            $lecturer = $lecturer_result->fetch_assoc();
                            echo "<br><b>👨‍🏫 Lecturer:</b> {$lecturer['lecturer_name']}<br>";
                            if (!empty($lecturer['email'])) {
                                echo "<b>📧 Email:</b> {$lecturer['email']}<br>";
                            }
                            if (!empty($lecturer['office'])) {
                                echo "<b>🏢 Office:</b> {$lecturer['office']}<br>";
                            }
                        }
                        $lecturer_stmt->close();
                    }
                }
                
                // Check if student is registered for this unit
                $student_reg = $_SESSION['reg_number'] ?? null;
                if ($student_reg) {
                    $check_reg_query = "SELECT status FROM registered_courses WHERE student_reg_no = ? AND unit_code = ?";
                    $check_reg_stmt = $conn->prepare($check_reg_query);
                    if ($check_reg_stmt) {
                        $check_reg_stmt->bind_param("ss", $student_reg, $unit_code);
                        $check_reg_stmt->execute();
                        $check_reg_result = $check_reg_stmt->get_result();
                        
                        if ($check_reg_result && $check_reg_result->num_rows > 0) {
                            $reg_status = $check_reg_result->fetch_assoc();
                            echo "<br><b>✅ Registration Status:</b> {$reg_status['status']}<br>";
                        } else {
                            echo "<br><b>⚠️ You are not registered for this unit yet.</b><br>";
                        }
                        $check_reg_stmt->close();
                    }
                }
                
                echo "<br><b>💡 What would you like to do next?</b><br>";
                echo "• Ask about another unit (e.g., 'Tell me about BIT2026')<br>";
                echo "• Check your timetable<br>";
                echo "• View your registered courses<br>";
                echo "• Get registration help<br>";
                
            } else {
                echo "❌ I couldn't find unit '{$unit_code}' in our system.<br><br>";
                
                // Try fuzzy search to suggest similar units
                $fuzzy_matches = fuzzySearchUnits($unit_code, $conn, 3);
                if (!empty($fuzzy_matches)) {
                    echo "<b>💡 Did you mean one of these?</b><br><br>";
                    foreach ($fuzzy_matches as $match) {
                        echo "• <b>{$match['unit_code']}</b> - {$match['unit_name']}<br>";
                    }
                    echo "<br>Try asking: 'Tell me about " . $fuzzy_matches[0]['unit_code'] . "'<br>";
                } else {
                    echo "💡 <i>Tip: Try searching with the correct unit code like 'BMA1106' or 'BIT2026'</i><br>";
                    echo "• Ask me to 'Show all first year units' to see available courses<br>";
                    echo "• Check your registered courses by asking 'My courses'<br>";
                }
            }
        }
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
            
            // Modified query to include day_of_week
            $query = "SELECT t.unit_code, t.course_title, t.day_of_week, t.time_from, t.time_to, t.venue, t.lecturer 
                     FROM timetable t 
                     INNER JOIN registered_courses rc ON t.unit_code = rc.unit_code 
                     WHERE rc.student_reg_no = ? AND t.semester = ? AND t.academic_year = ?
                     ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.time_from";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $student_reg, $current_semester, $current_year);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<b>📅 Your Class Schedule</b><br><br>";
                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<b>{$counter}. {$row['unit_code']} - {$row['course_title']}</b><br>";
                    
                    // Show the day prominently
                    if (!empty($row['day_of_week']) && $row['day_of_week'] != '0000-00-00') {
                        echo "   📆 <b>Day:</b> {$row['day_of_week']}<br>";
                    } else {
                        echo "   📆 <b>Day:</b> To be confirmed<br>";
                    }
                    
                    echo "   ⏰ <b>Time:</b> {$row['time_from']} - {$row['time_to']}<br>";
                    echo "   📍 <b>Venue:</b> {$row['venue']}<br>";
                    echo "   👨‍🏫 <b>Lecturer:</b> {$row['lecturer']}<br><br>";
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
            // Show registered units
            $registered_query = "SELECT rc.unit_code, aw.unit_name, rc.status, rc.academic_year, aw.year_level, aw.semester_level
                                FROM registered_courses rc 
                                LEFT JOIN academic_workload aw ON rc.unit_code = aw.unit_code 
                                WHERE rc.student_reg_no = ? 
                                ORDER BY aw.year_level, aw.semester_level";
            $registered_stmt = $conn->prepare($registered_query);
            $registered_stmt->bind_param("s", $student_reg);
            $registered_stmt->execute();
            $registered_result = $registered_stmt->get_result();
            
            if ($registered_result->num_rows > 0) {
                echo "<b>📚 Your Registered Courses</b><br><br>";
                $current_year = '';
                while ($row = $registered_result->fetch_assoc()) {
                    if ($current_year != $row['academic_year']) {
                        if ($current_year != '') echo "<br>";
                        $current_year = $row['academic_year'];
                        echo "<b>Academic Year: {$current_year}</b><br>";
                    }
                    echo "• {$row['unit_code']} - " . ($row['unit_name'] ?? 'Unit Name Not Found') . "<br>";
                    echo "  <span style='font-size:0.9em; color:#666;'>{$row['year_level']}, {$row['semester_level']} • Status: {$row['status']}</span><br>";
                }
                echo "<br><hr><br>";
            } else {
                echo "<b>⚠️ You haven't registered for any units yet.</b><br><br><hr><br>";
            }
            
            // Show ALL available units
            $all_units_query = "SELECT unit_code, unit_name, year_level, semester_level, offering_time 
                               FROM academic_workload 
                               ORDER BY year_level, semester_level, unit_code";
            $all_units_result = mysqli_query($conn, $all_units_query);
            
            if (mysqli_num_rows($all_units_result) > 0) {
                echo "<b>📚 All Available Units</b><br><br>";
                
                $units_by_year = [];
                while ($row = mysqli_fetch_assoc($all_units_result)) {
                    $year = $row['year_level'];
                    $semester = $row['semester_level'];
                    if (!isset($units_by_year[$year])) {
                        $units_by_year[$year] = [];
                    }
                    if (!isset($units_by_year[$year][$semester])) {
                        $units_by_year[$year][$semester] = [];
                    }
                    $units_by_year[$year][$semester][] = $row;
                }
                
                foreach ($units_by_year as $year => $semesters) {
                    echo "<b>📖 {$year}</b><br>";
                    foreach ($semesters as $semester => $units) {
                        echo "<b>  📌 {$semester}:</b><br>";
                        foreach ($units as $unit) {
                            echo "    • <b>{$unit['unit_code']}</b> - {$unit['unit_name']}<br>";
                            echo "      <span style='font-size:0.85em; color:#666;'>Offered: {$unit['offering_time']}</span><br>";
                        }
                        echo "<br>";
                    }
                    echo "<br>";
                }
                
                echo "💡 <i>Need to register for units? Just ask me 'how to register' and I'll guide you!</i>";
            } else {
                echo "📭 No units found in the system. Please contact the academic office.";
            }
        } else {
            echo "🔐 Please log in to view your courses.";
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
        
        // First try exact match
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
            echo "💡 <i>Want more details about any of these units? Just ask me!</i>";
        } else {
            // Try fuzzy search
            $fuzzy_matches = fuzzySearchUnits($search_term, $conn);
            
            if (!empty($fuzzy_matches)) {
                echo "<b>🔍 Did you mean one of these?</b><br><br>";
                foreach ($fuzzy_matches as $match) {
                    echo "• <b>{$match['unit_code']}</b> - {$match['unit_name']}<br>";
                    echo "  <span style='font-size:0.85em; color:#666;'>Matched on: {$match['matched_on']} (" . round($match['score']) . "% similar)</span><br><br>";
                }
                echo "💡 <i>Try asking for details about any of these units!</i>";
            } else {
                echo "🔍 Hmm, I couldn't find '$search_term' in our system. 🤔<br><br>";
                echo "💡 <i>Tips for better results:</i><br>";
                echo "• Check the unit code format (e.g., BMA1106)<br>";
                echo "• Try spelling the unit name correctly<br>";
                echo "• Ask me to list all units for your year level<br><br>";
                echo "Would you like me to show you all available units?";
            }
        }
        break;

    case 'reg_help':
        $student_reg = $_SESSION['reg_number'] ?? null;
        $student_name = $_SESSION['user_name'] ?? 'Student';
        
        if ($student_reg) {
            // Get student's registered courses to determine their year level
            $year_query = "SELECT DISTINCT aw.year_level 
                          FROM registered_courses rc 
                          JOIN academic_workload aw ON rc.unit_code = aw.unit_code 
                          WHERE rc.student_reg_no = ? 
                          LIMIT 1";
            $year_stmt = $conn->prepare($year_query);
            $year_stmt->bind_param("s", $student_reg);
            $year_stmt->execute();
            $year_result = $year_stmt->get_result();
            
            $student_year = 'First Year'; // Default
            if ($year_result->num_rows > 0) {
                $student_year = $year_result->fetch_assoc()['year_level'];
            }
            
            // Get available units for student's year
            $units_query = "SELECT unit_code, unit_name, semester_level, offering_time 
                           FROM academic_workload 
                           WHERE year_level = ? 
                           ORDER BY semester_level, unit_code";
            $units_stmt = $conn->prepare($units_query);
            $units_stmt->bind_param("s", $student_year);
            $units_stmt->execute();
            $units_result = $units_stmt->get_result();
            
            // Get already registered units
            $registered_query = "SELECT unit_code FROM registered_courses WHERE student_reg_no = ?";
            $registered_stmt = $conn->prepare($registered_query);
            $registered_stmt->bind_param("s", $student_reg);
            $registered_stmt->execute();
            $registered_result = $registered_stmt->get_result();
            $registered_units = [];
            while ($row = $registered_result->fetch_assoc()) {
                $registered_units[] = $row['unit_code'];
            }
            
            echo "<b>🎓 Course Registration Guide</b><br><br>";
            echo "Hi $student_name! I'll help you register for your $student_year units.<br><br>";
            
            echo "<b>📋 Step-by-Step Registration Process:</b><br>";
            echo "1️⃣ <b>Review Available Units:</b> Check the units listed below for your year level<br>";
            echo "2️⃣ <b>Select Your Units:</b> Choose the units you want to register for<br>";
            echo "3️⃣ <b>Submit Registration:</b> Go to the registration portal and confirm your selection<br>";
            echo "4️⃣ <b>Wait for Confirmation:</b> Your registration will be processed within 24-48 hours<br><br>";
            
            if ($units_result->num_rows > 0) {
                echo "<b>📚 Available Units for {$student_year}:</b><br><br>";
                $units_by_semester = [];
                while ($row = $units_result->fetch_assoc()) {
                    $semester = $row['semester_level'];
                    if (!isset($units_by_semester[$semester])) {
                        $units_by_semester[$semester] = [];
                    }
                    $units_by_semester[$semester][] = $row;
                }
                
                foreach ($units_by_semester as $semester => $units) {
                    echo "<b>📌 {$semester}:</b><br>";
                    foreach ($units as $unit) {
                        $status = in_array($unit['unit_code'], $registered_units) ? "✅ Already Registered" : "⚪ Available";
                        echo "  • <b>{$unit['unit_code']}</b> - {$unit['unit_name']}<br>";
                        echo "    <span style='font-size:0.85em; color:#666;'>Offered: {$unit['offering_time']} • {$status}</span><br>";
                    }
                    echo "<br>";
                }
            } else {
                echo "⚠️ No units found for your year level. Please contact the academic office.<br><br>";
            }
            
            if (!empty($registered_units)) {
                echo "<b>✅ You're currently registered for:</b> " . implode(", ", $registered_units) . "<br><br>";
            }
            
            echo "<b>💡 Pro Tips:</b><br>";
            echo "• Register early to secure your preferred units<br>";
            echo "• Make sure you meet all prerequisites for your chosen units<br>";
            echo "• You can register for a maximum of 8 units per semester<br>";
            echo "• Contact the academic advisor if you need help selecting units<br><br>";
            
            echo "Would you like me to help you with anything else? You can ask me to:<br>";
            echo "• Show specific unit details (e.g., 'Tell me about BMA1106')<br>";
            echo "• Check unit prerequisites<br>";
            echo "• View your current timetable<br>";
            echo "• See all available units<br>";
            
        } else {
            echo "<b>🎓 Course Registration Guide</b><br><br>";
            echo "To register for units, please follow these steps:<br><br>";
            echo "1️⃣ <b>Log in to the portal</b> using your registration number and password<br>";
            echo "2️⃣ <b>Navigate to the Registration section</b> in the main menu<br>";
            echo "3️⃣ <b>Select your year level</b> to view available units<br>";
            echo "4️⃣ <b>Choose your units</b> and confirm your selection<br>";
            echo "5️⃣ <b>Submit your registration</b> before the deadline<br><br>";
            
            // Show all available units for preview
            $all_units_query = "SELECT unit_code, unit_name, year_level, semester_level, offering_time 
                               FROM academic_workload 
                               ORDER BY year_level, semester_level, unit_code";
            $all_units_result = mysqli_query($conn, $all_units_query);
            
            if (mysqli_num_rows($all_units_result) > 0) {
                echo "<b>📚 Preview of Available Units:</b><br><br>";
                $units_by_year = [];
                while ($row = mysqli_fetch_assoc($all_units_result)) {
                    $year = $row['year_level'];
                    $semester = $row['semester_level'];
                    if (!isset($units_by_year[$year])) {
                        $units_by_year[$year] = [];
                    }
                    if (!isset($units_by_year[$year][$semester])) {
                        $units_by_year[$year][$semester] = [];
                    }
                    $units_by_year[$year][$semester][] = $row;
                }
                
                foreach ($units_by_year as $year => $semesters) {
                    echo "<b>📖 {$year}</b><br>";
                    foreach ($semesters as $semester => $units) {
                        echo "<b>  📌 {$semester}:</b><br>";
                        foreach ($units as $unit) {
                            echo "    • <b>{$unit['unit_code']}</b> - {$unit['unit_name']}<br>";
                        }
                        echo "<br>";
                    }
                }
            }
            
            echo "🔐 <i>Please log in first so I can show you the units available for your specific year level and help you register!</i>";
        }
        break;

    case 'ai_check':
        echo "🤖 I'm your friendly AI academic assistant! Here's what I can do:<br><br>
              ✅ <b>Course Advisor</b> - Get personalized course recommendations (e.g., 'What courses should I take?')<br>
              ✅ Show units by year level (e.g., 'Show me second year units')<br>
              ✅ Search for specific units by code or name<br>
              ✅ Get detailed unit information (e.g., 'Tell me about BMA1106')<br>
              ✅ Display your timetable with days and times (login required)<br>
              ✅ Find out when a specific unit is taught (e.g., 'When is BIT3208 taught?')<br>
              ✅ Show your exam schedule (login required)<br>
              ✅ List your registered courses (login required)<br>
              ✅ Show all available units (login required)<br>
              ✅ Guide you through course registration (e.g., 'how to register')<br>
              ✅ Define words and build vocabulary (e.g., 'What does algorithm mean?')<br><br>
              💬 And I love small talk too! Ask me how I'm doing! 😊<br><br>
              🎓 <b>Try asking:</b> 'What courses should I take?' for personalized registration advice!<br><br>
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