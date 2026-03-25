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
if (!$conn) { die("Connection failed"); }

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
    3. MACHINE LEARNING ENGINE
================================ */
// Training data for intent classification
$samples = [
    'hi', 'hello', 'hey', 'good morning',            // greet
    'unit', 'units', 'show me units', 'workload',    // view_all
    'first year', 'second year', 'third year',       // view_all (text versions)
    'math', 'programming', 'database', 'bit1101',    // search_unit
    'registration', 'register', 'enroll',            // reg_help
    'ai', 'artificial intelligence', 'bot info'      // ai_check
];

$labels = [
    'greet', 'greet', 'greet', 'greet',
    'view_all', 'view_all', 'view_all', 'view_all',
    'view_all', 'view_all', 'view_all',
    'search_unit', 'search_unit', 'search_unit', 'search_unit',
    'reg_help', 'reg_help', 'reg_help',
    'ai_check', 'ai_check', 'ai_check'
];

$vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
$vectorizer->fit($samples);
$vectorizer->transform($samples);
$transformer = new TfIdfTransformer($samples);
$transformer->transform($samples);
$classifier = new NaiveBayes();
$classifier->train($samples, $labels);

// Check if we should even use ML or just fallback immediately
$local_keywords = ['unit', 'units', 'course', 'workload', '1.1', '1.2', '2.1', '2.2', 'register', 'enroll', 'hi', 'ai', 'math', 'java', 'programming', 'database', 'year'];
$found_local_match = false;
foreach ($local_keywords as $word) {
    if (strpos($user_input, $word) !== false) { $found_local_match = true; break; }
    similar_text($user_input, $word, $percent);
    if ($percent > 80) { $found_local_match = true; $user_input = $word; break; }
}

if (!$found_local_match) {
    $intent = 'fallback';
} else {
    $test_sample = [$user_input];
    $vectorizer->transform($test_sample);
    $transformer->transform($test_sample);
    $intent = $classifier->predict($test_sample)[0];
}

/* ===============================
    4. FULL ACTION LOGIC
================================ */
switch ($intent) {
    case 'greet':
        $greetings = [
            "Hello! I am your AI Assistant. How can I help you with your units today?",
            "Hi there! I'm ready to help you search the Master Plan. What's on your mind?",
            "Greetings! You can ask me for a full workload (e.g., 'units for 1.1') or search for a specific unit."
        ];
        echo $greetings[array_rand($greetings)];
        break;

    case 'search_unit':
        $search_term = "%$user_input%";
        $s_stmt = $conn->prepare("SELECT unit_code, unit_name, year_level FROM academic_workload WHERE unit_name LIKE ? OR unit_code LIKE ?");
        $s_stmt->bind_param("ss", $search_term, $search_term);
        $s_stmt->execute();
        $s_res = $s_stmt->get_result();

        if ($s_res->num_rows > 0) {
            $starters = ["I found these matching units:", "Here is what the Master Plan says:", "Matching results:"];
            echo "<b>" . $starters[array_rand($starters)] . "</b><br>";
            while ($row = $s_res->fetch_assoc()) {
                echo "• " . $row['unit_code'] . " - " . $row['unit_name'] . " (" . $row['year_level'] . ")<br>";
            }
        } else {
            // FUZZY "DID YOU MEAN" LOGIC
            $all_units = mysqli_query($conn, "SELECT unit_name FROM academic_workload");
            $closest_match = null;
            $shortest_dist = -1;

            while ($unit = mysqli_fetch_assoc($all_units)) {
                $lev = levenshtein($user_input, strtolower($unit['unit_name']));
                if ($lev <= 3 && ($lev < $shortest_dist || $shortest_dist == -1)) {
                    $closest_match = $unit['unit_name'];
                    $shortest_dist = $lev;
                }
            }

            if ($closest_match) {
                echo "I couldn't find a direct match for '$user_input'. Did you mean <b>$closest_match</b>?";
            } else {
                goto fallback_logic; 
            }
        }
        break;

    case 'view_all':
        $year_map = ['1' => 'First Year', '2' => 'Second Year', '3' => 'Third Year', '4' => 'Fourth Year'];
        $sem_map = ['1' => '1st Semester', '2' => '2nd Semester'];

        $target_year = 'First Year';
        $target_sem = '1st Semester';

        if (preg_match('/([1-4])\.([1-2])/', $_POST['message'], $matches)) {
            $target_year = $year_map[$matches[1]];
            $target_sem = $sem_map[$matches[2]];
        }

        $w_stmt = $conn->prepare("SELECT unit_code, unit_name FROM academic_workload WHERE year_level = ? AND semester_level = ?");
        $w_stmt->bind_param("ss", $target_year, $target_sem);
        $w_stmt->execute();
        $w_res = $w_stmt->get_result();

        if ($w_res->num_rows > 0) {
            $intros = [
                "For $target_year ($target_sem), the units are as follows:",
                "I've retrieved the workload for $target_year $target_sem:",
                "According to the Master Plan, you'll be taking these units in $target_year:"
            ];
            echo "<b>" . $intros[array_rand($intros)] . "</b><br>";
            while ($row = $w_res->fetch_assoc()) {
                echo "• " . $row['unit_code'] . " - " . $row['unit_name'] . "<br>";
            }
        } else {
            echo "I couldn't find units for $target_year $target_sem in the system.";
        }
        break;

    case 'fallback':
    default:
        fallback_logic:
        // Save user message
        $stmt_msg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'student', ?)");
        $stmt_msg->bind_param("ss", $sess_id, $user_input);
        $stmt_msg->execute();

        $ai_response = callGeminiAPI($user_input);
        $student_name = $_SESSION['user_name'] ?? 'Guest Student';

        if ($ai_response == "OFFLINE_ESCALATE") {
            // Referral Logic
            $check_ref = $conn->prepare("SELECT id FROM admin_referrals WHERE conversation_id = ? AND status = 'pending'");
            $check_ref->bind_param("s", $sess_id);
            $check_ref->execute();
            
            if ($check_ref->get_result()->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO admin_referrals (sender_name, status, conversation_id) VALUES (?, 'pending', ?)");
                $stmt->bind_param("ss", $student_name, $sess_id);
                $stmt->execute();
            }

            $bot_msg = "I'm not sure about that. I've forwarded your query to the Admin inbox for you!";
            echo $bot_msg;
            
            $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
            $stmt_bot->bind_param("ss", $sess_id, $bot_msg);
            $stmt_bot->execute();
        } else {
            echo $ai_response;
            $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
            $stmt_bot->bind_param("ss", $sess_id, $ai_response);
            $stmt_bot->execute();
        }
        break; 
}

/* ===============================
    5. THE GEMINI API FUNCTION
================================ */
function callGeminiAPI($message) {
    $apiKey = "AIzaSyDZKvwEH5dxLcMmnmVBvOBgh5RyBP0lLTo"; 
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
    $systemPrompt = "You are an MKU Student Assistant. Be helpful and natural. Return 'OFFLINE_ESCALATE' for specific student grades or fee balances.";
    
    $data = ["contents" => [["parts" => [["text" => $systemPrompt . "\n\nStudent: " . $message]]]]];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $result = curl_exec($ch);
    $response = json_decode($result, true);
    curl_close($ch);
    
    return trim($response['candidates'][0]['content']['parts'][0]['text'] ?? "OFFLINE_ESCALATE");
}
?>