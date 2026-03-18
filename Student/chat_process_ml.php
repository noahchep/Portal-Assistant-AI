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
$samples = ['hi', 'hello', 'units', 'show me units', 'workload', 'registration', 'register', 'ai', 'artificial intelligence'];
$labels = ['greet', 'greet', 'view_all', 'view_all', 'view_all', 'reg_help', 'reg_help', 'ai_check', 'ai_check'];

$vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
$vectorizer->fit($samples);
$vectorizer->transform($samples);
$transformer = new TfIdfTransformer($samples);
$transformer->transform($samples);
$classifier = new NaiveBayes();
$classifier->train($samples, $labels);

$local_keywords = ['unit', 'course', 'workload', '3.1', '3.2', '2.1', '2.2', 'register', 'enroll', 'hi', 'ai', 'artificial'];
$found_local_match = false;
foreach ($local_keywords as $word) {
    if (strpos($user_input, $word) !== false) { $found_local_match = true; break; }
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
        echo "Hello! I am your AI Assistant. How can I help you today?";
        break;

    case 'view_all':
        // Your logic for academic_workload table
        echo "Listing available units...";
        break;

    case 'fallback':
    default:
        // FIRST: Log the student's message so the Admin sees it in history
        $stmt_msg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'student', ?)");
        $stmt_msg->bind_param("ss", $sess_id, $user_input);
        $stmt_msg->execute();

        // SECOND: Call the Gemini API
        $ai_response = callGeminiAPI($user_input);
        $student_name = $_SESSION['user_name'] ?? 'Guest Student';

        if ($ai_response == "OFFLINE_ESCALATE") {
            // Check for existing referral to avoid duplicates
            $check_ref = $conn->prepare("SELECT id FROM admin_referrals WHERE conversation_id = ? AND status = 'pending'");
            $check_ref->bind_param("s", $sess_id);
            $check_ref->execute();
            
            if ($check_ref->get_result()->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO admin_referrals (sender_name, status, conversation_id) VALUES (?, 'pending', ?)");
                $stmt->bind_param("ss", $student_name, $sess_id);
                $stmt->execute();
            }

            $bot_msg = "I'm not sure about that. I've forwarded your query to the Admin inbox for you!";
            $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
            $stmt_bot->bind_param("ss", $sess_id, $bot_msg);
            $stmt_bot->execute();
            echo $bot_msg;
        } else {
            // Log the successful Gemini response
            $stmt_bot = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'bot', ?)");
            $stmt_bot->bind_param("ss", $sess_id, $ai_response);
            $stmt_bot->execute();
            echo $ai_response;
        }
        break; 
}

/* ===============================
    5. THE GEMINI API FUNCTION
================================ */
function callGeminiAPI($message) {
    $apiKey = "YOUR_ACTUAL_API_KEY_HERE"; 
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
    
    // This prompt forces Gemini to escalate sensitive topics
    $systemPrompt = "You are an MKU Student Assistant. Be helpful. If a question is about specific student grades, specific fee balances, or any technical issue you cannot solve, return exactly 'OFFLINE_ESCALATE'.";
    
    $data = [
        "contents" => [
            ["parts" => [["text" => $systemPrompt . "\n\nStudent: " . $message]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing
    
    $result = curl_exec($ch);
    $response = json_decode($result, true);
    curl_close($ch);
    
    $text_response = $response['candidates'][0]['content']['parts'][0]['text'] ?? "OFFLINE_ESCALATE";
    return trim($text_response);
}
?>