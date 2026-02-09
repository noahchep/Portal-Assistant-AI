<?php
session_start();

// 1. Database connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "Portal-Asisstant-AI";

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    $sql = "SELECT * FROM `users` WHERE `reg_number` = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            // FIX: We must clear any old session data first
            session_regenerate_id(true);

            // SET SESSIONS
            $_SESSION['user_id']    = $row['id'];
            $_SESSION['user_name']  = $row['full_name'];
            $_SESSION['role']       = $row['role'];
            
            // CRITICAL ADDITION: This allows registration.php to see ONLY this student's units
            $_SESSION['reg_number'] = $row['reg_number']; 

            if ($row['role'] == 'admin') {
                header("Location: Admin/Admin-index.php");
            } else {
                header("Location: Student/home.php");
            }
            exit();
        } else {
            $error = "Invalid Registration Number or Password!";
        }
    } else {
        $error = "Invalid Registration Number or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MKU Portal Assistant | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            background: url("../Images/logback.jpg") no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 900px;
            display: flex;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #003366, #0056b3);
            color: #fff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .logo-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid white;
            margin-bottom: 20px;
            padding: 5px;
            background: white;
        }

        .login-left h2 { margin: 10px 0; font-size: 24px; }
        .login-left p { font-size: 14px; opacity: 0.9; font-style: italic; }

        .login-right {
            flex: 1.2;
            padding: 50px;
            background: white;
        }

        .login-title {
            color: #003366;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .slogan {
            font-size: 12px;
            color: #666;
            margin-bottom: 30px;
            display: block;
        }

        .form-group { margin-bottom: 20px; }

        .control-label {
            display: block;
            font-weight: 600;
            color: #003366;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Poppins';
            outline: none;
            transition: border 0.3s;
        }

        .form-control:focus { border-color: #0056b3; }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #003366;
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.3);
            transition: 0.3s;
        }

        .btn-login:hover { background: #002244; transform: translateY(-2px); }

        .forgot-link {
            text-align: right;
            margin-top: 15px;
            font-size: 12px;
        }

        .forgot-link a { color: #dc3545; text-decoration: none; font-weight: bold; }

        marquee {
            margin-top: 30px;
            background: #f0f4f8;
            padding: 8px;
            border-radius: 5px;
            color: #003366;
            font-size: 11px;
            border: 1px solid #d0dae5;
        }

        .error-msg {
            color: #dc3545;
            background: #fdecea;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #fabeb6;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <img src="../Images/logo.jpg" class="logo-circle" alt="MKU Logo">
        <h2>Portal Assistant AI</h2>
        <p>Infinite support for infinite possibilities.</p>
        <div style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 20px;">
            <small>System Version 2026.1</small>
        </div>
    </div>

    <div class="login-right">
        <div class="login-title">Portal Assistant AI</div>
        <span class="slogan">Infinite support for infinite possibilities.</span>

        <?php if($error != "") { echo '<div class="error-msg">'.$error.'</div>'; } ?>

        <form action="" method="post">
            <div class="form-group">
                <label class="control-label">User ID / Reg Number</label>
                <input type="text" name="username" class="form-control" placeholder="e.g. BIT/2024/43255" required>
            </div>

            <div class="form-group">
                <label class="control-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <input type="submit" name="signin" class="btn-login" value="SIGN IN">
            
            <div class="forgot-link">
                <a href="#">Forgot Password?</a>
            </div>

            <marquee behavior="scroll" direction="left">
                🔔 <strong>Notice:</strong> Unit registration for Jan/Apr 2026 semester is now open. Portal Assistant AI is available to guide you.
            </marquee>
        </form>
    </div>
</div>

</body>
</html>