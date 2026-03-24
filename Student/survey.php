<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $challenge = mysqli_real_escape_string($conn, $_POST['challenge']);
    $ease = mysqli_real_escape_string($conn, $_POST['ease']);

    // Save the data to your new survey table
    $sql = "INSERT INTO survey_responses (user_id, challenge_type, ease_rating) VALUES ('$user_id', '$challenge', '$ease')";
    
    if (mysqli_query($conn, $sql)) {
        // Mark survey as done so they don't see it again
        mysqli_query($conn, "UPDATE users SET survey_done = 1 WHERE id = '$user_id'");
        
        /* FIXED REDIRECT: Sending back to your actual registration file */
        header("Location: registration.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback | Student Support AI</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; border: 1px solid #e2e8f0; }
        h2 { color: #4f46e5; margin-bottom: 10px; }
        p { color: #64748b; font-size: 0.9rem; margin-bottom: 25px; }
        .option { display: block; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: 0.2s; }
        .option:hover { background: #f1f5f9; border-color: #4f46e5; }
        .btn { background: #4f46e5; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer; margin-top: 15px; }
        input[type="radio"] { margin-right: 10px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Help us improve!</h2>
    <p>We noticed you just registered for units. Please help our research by answering two questions.</p>
    
    <form method="POST">
        <label><strong>What was your biggest challenge?</strong></label>
        <label class="option"><input type="radio" name="challenge" value="Finding Codes" required> Finding correct unit codes</label>
        <label class="option"><input type="radio" name="challenge" value="System Speed"> System slow/loading loops</label>
        <label class="option"><input type="radio" name="challenge" value="User Interface"> Complex navigation/UI</label>
        
        <label style="display:block; margin-top:20px;"><strong>Overall Ease (1-5):</strong></label>
        <select name="ease" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0; margin-top:5px;">
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Good</option>
            <option value="3">3 - Average</option>
            <option value="2">2 - Poor</option>
            <option value="1">1 - Very Difficult</option>
        </select>

        <button type="submit" class="btn">Submit & Continue</button>
    </form>
</div>

</body>
</html>