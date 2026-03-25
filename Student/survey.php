<?php
session_start();

if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");

if (!$conn) {
    die("Connection failed: " . mysqli_error($conn));
}

$user_id = $_SESSION['user_id'];

// Fetch student name for the header greeting
$user_query = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$student_name = $user_data['full_name'] ?? 'Student';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $challenge = mysqli_real_escape_string($conn, $_POST['challenge']);
    $ease = mysqli_real_escape_string($conn, $_POST['ease']);

    $sql = "INSERT INTO survey_responses (user_id, challenge_type, ease_rating) 
            VALUES ('$user_id', '$challenge', '$ease')";
    
    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "UPDATE users SET survey_done = 1 WHERE id = '$user_id'");
        header("Location: registration.php?survey_complete=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Survey | Portal Assistant</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        /* HEADER & NAV STYLES (Matching your Registration Page) */
        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; }
        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 50px; border-radius: 8px; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; }
        .branding small { color: var(--text-light); display: block; font-size: 0.85rem; }

        nav { background: var(--primary); padding: 0 5%; display: flex; gap: 10px; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; }
        nav a.active { color: white; background: rgba(255,255,255,0.15); border-bottom: 3px solid white; }

        /* SURVEY CONTENT STYLES */
        .container { max-width: 600px; margin: 50px auto; padding: 0 20px; }
        .card { background: var(--white); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 30px; border: 1px solid var(--border); }
        h2 { color: var(--primary); margin-top: 0; font-size: 1.5rem; }
        p { color: var(--text-light); margin-bottom: 25px; }

        .option { display: block; padding: 12px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: 0.2s; font-size: 0.9rem; }
        .option:hover { background: #f1f5f9; border-color: var(--primary); }
        
        .btn { background: var(--primary); color: white; border: none; padding: 14px; border-radius: 8px; width: 100%; font-weight: 700; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn:hover { background: var(--primary-dark); }

        select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: #fafafa; margin-top: 10px; }
        
        footer { text-align: center; padding: 40px; color: var(--text-light); font-size: 0.85rem; }
    </style>
</head>
<body>

<header>
    <div class="branding">
        <img src="../Images/logo.jpg" class="logoimg" alt="Logo">
        <div>
            <h1>Student Support Agent</h1>
            <small>Infinite support for infinite possibilities.</small>
        </div>
    </div>
    <div style="font-size: 0.85rem; color: var(--text-light);">
        Academic Year: <strong>2026</strong> | Semester: <strong>Jan-Apr</strong>
    </div>
</header>

<nav>
    <a href="Home.php">Home</a>
    <a href="personal_information.php">Information Update</a>
    <a href="#">Fees</a>
    <a href="teaching_timetable.php">Timetables</a>
    <a href="registration.php" class="active">Course Registration</a>
    <a href="#">Sign Out</a>
</nav>

<div class="container">
    <div class="card">
        <h2>Help us improve, <?php echo explode(' ', $student_name)[0]; ?>!</h2>
        <p>Please answer these two quick questions to finish confirming your units.</p>
        
        <form method="POST">
            <label><strong>What was your biggest challenge today?</strong></label>
            <div style="margin-top: 10px;">
                <label class="option"><input type="radio" name="challenge" value="Finding Codes" required> Finding correct unit codes</label>
                <label class="option"><input type="radio" name="challenge" value="System Speed"> System slow/loading loops</label>
                <label class="option"><input type="radio" name="challenge" value="User Interface"> Complex navigation/UI</label>
            </div>
            
            <label style="display:block; margin-top:20px;"><strong>Overall Ease of Registration (1-5):</strong></label>
            <select name="ease" required>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Difficult</option>
            </select>
            
            <button type="submit" class="btn">Submit & Finalize Registration</button>
        </form>
    </div>
</div>

<footer>
    &copy; 2026 Mount Kenya University | Portal Assistant AI System
</footer>

</body>
</html>