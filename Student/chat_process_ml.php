<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Phpml\Classification\NaiveBayes;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\FeatureExtraction\TfIdfTransformer;

/* ===============================
    1. DATABASE CONNECTION
================================ */
$conn = mysqli_connect("localhost", "root", "", "portal-asisstant-ai");
if (!$conn) { die("Database Connection Failed"); }

/* ===============================
    2. TIME & SEMESTER CALCULATION
================================ */
$month = (int)date('n'); 
if ($month >= 1 && $month <= 4) {
    $current_sem_label = "1st Semester"; 
    $season = "January - April";
    $next_sem = "2nd Semester"; 
} elseif ($month >= 5 && $month <= 8) {
    $current_sem_label = "2nd Semester"; 
    $season = "May - August";
    $next_sem = "1st Semester"; 
} else {
    $current_sem_label = "1st Semester"; 
    $season = "September - December";
    $next_sem = "1st Semester"; 
}

/* ===============================
    3. MACHINE LEARNING TRAINING
================================ */
$samples = [
    'hi', 'hello', 'hey', 'good morning',                       
    'show me units', 'what are the courses', 'workload', 'units on offer',        
    'year 1 units', 'first year subjects', 'bit year 1', '3.1', '3.2', '2.2', '4.1',        
    'advice on registration', 'what should i register',         
    'how to register', 'enrollment process', 'sign up',
    'will artificial intelligence be on offer', 'is ai available next semester', 'check ai' 
];
$labels = [
    'greet', 'greet', 'greet', 'greet',
    'view_all', 'view_all', 'view_all', 'view_all',
    'view_y1', 'view_y1', 'view_y1', 'view_y1', 'view_y1', 'view_y1', 'view_y1',
    'get_advice', 'get_advice',
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

/* ===============================
    4. PREDICTION
================================ */
$user_input = strtolower(trim($_POST['message'] ?? ''));
if ($user_input == "") die("No message received");

$test_sample = [$user_input];
$vectorizer->transform($test_sample);
$transformer->transform($test_sample);

$intent = $classifier->predict($test_sample)[0];

/* ===============================
    5. ACTION LOGIC
================================ */
switch ($intent) {
    case 'greet':
        echo "Hello! I am your MKU Assistant. I've analyzed the current trimester ($season). How can I help you today?";
        break;

    case 'view_y1':
        // Check if user mentioned a specific stage like "3.2" or "2.1"
        if (preg_match('/(\d)\.(\d)/', $user_input, $matches)) {
            $year_num = $matches[1];
            $sem_num = $matches[2];

            // Map numbers to Database strings
            $year_map = ["1" => "First Year", "2" => "Second Year", "3" => "Third Year", "4" => "Fourth Year"];
            $sem_map = ["1" => "1st Semester", "2" => "2nd Semester"];

            $y_text = $year_map[$year_num] ?? "First Year";
            $s_text = $sem_map[$sem_num] ?? "1st Semester";

            $res = mysqli_query($conn, "SELECT * FROM academic_workload WHERE year_level = '$y_text' AND (semester_level = '$s_text' OR offering_time = 'Every Semester')");
            echo "<b>Units for $y_text ($s_text):</b><br>";
        } else {
            // Default to Year 1 Current Semester
            $res = mysqli_query($conn, "SELECT * FROM academic_workload WHERE year_level = 'First Year' AND (semester_level = '$current_sem_label' OR offering_time = 'Every Semester')");
            echo "<b>First Year units currently on offer ($season):</b><br>";
        }

        while($row = mysqli_fetch_assoc($res)) {
            echo "• {$row['unit_code']} - {$row['unit_name']}<br>";
        }
        break;

    case 'get_advice':
        $res = mysqli_query($conn, "SELECT * FROM academic_workload WHERE year_level = 'First Year'");
        echo "<b>Strategic Advice for your Current Session ($season):</b><br><br>";
        while($row = mysqli_fetch_assoc($res)) {
            $code = $row['unit_code']; $name = $row['unit_name'];
            $offering = $row['offering_time']; $unitSem = $row['semester_level'];

            echo "• <b>$code</b>: $name <br>";
            if ($offering == "Every Semester") {
                echo "<i>&nbsp;&nbsp;Status: Flexible. Available every semester.</i><br><br>";
            } elseif ($offering == "Once a Year" && $unitSem == $current_sem_label) {
                echo "<i>&nbsp;&nbsp;<b><span style='color:red;'>Advice: PRIORITY.</span></b> This is only offered once a year in the $unitSem. Register now!</i><br><br>";
            } else {
                echo "<i>&nbsp;&nbsp;Status: Not on offer. Scheduled for the $unitSem.</i><br><br>";
            }
        }
        break;

    case 'ai_check':
        $search = mysqli_real_escape_string($conn, "Artificial Intelligence");
        $res = mysqli_query($conn, "SELECT * FROM academic_workload WHERE unit_name LIKE '%$search%' LIMIT 1");
        $row = mysqli_fetch_assoc($res);

        if ($row) {
            $unit_sem = $row['semester_level'];
            $offering = $row['offering_time'];
            echo "<b>Analysis for " . $row['unit_name'] . ":</b><br>";

            if ($unit_sem == $next_sem || $offering == "Every Semester") {
                echo "✅ <b>YES.</b> This unit will be on offer next semester ($next_sem).";
            } else {
                echo "❌ <b>NO.</b> It is scheduled for the <u>$unit_sem</u>. Since it is '$offering', you should wait.";
            }
        } else {
            echo "I couldn't find that unit in the Master Plan.";
        }
        break;

    case 'view_all':
        // Only show units actually on offer THIS semester
        $res = mysqli_query($conn, "SELECT * FROM academic_workload WHERE semester_level = '$current_sem_label' OR offering_time = 'Every Semester'");
        echo "<b>Units on Offer this Semester ($season):</b><br>";
        while($row = mysqli_fetch_assoc($res)) {
            echo "✅ <b>{$row['unit_code']}</b>: {$row['unit_name']}<br>";
        }
        break;

    case 'reg_help':
        echo "Ensure your fee balance is zero. Go to 'Course Registration' and select available units for $season.";
        break;

    default:
        echo "I'm still learning! Try asking 'What units are on offer?' or 'What are the units for 3.2?'";
        break;
}
?>