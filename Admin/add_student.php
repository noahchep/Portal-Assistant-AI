<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* SECURITY CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Administrator';

/* DB CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) { die("Database connection failed"); }

$message = "";

/* HANDLE FORM LOGIC */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_student'])) {
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $dept = $_POST['department'];
    $full_name = trim("$fname $mname $lname");

    // Programme Codes
    $codes = [
        "Information Technology" => ["BIT", 4],
        "Computer Science" => ["BSCCS", 4],
        "Enterprise Computing" => ["BBIT", 5],
        "Information Science & Knowledge Management" => ["BIS", 6]
    ];
    
    $p_code = $codes[$dept][0] ?? "XXXX";
    $p_pad = $codes[$dept][1] ?? 4;
    $year = date("Y");

    // Sequence generation
    $stmt_c = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE department=? AND reg_number LIKE ?");
    $like = "$p_code/$year/%";
    $stmt_c->bind_param("ss", $dept, $like);
    $stmt_c->execute();
    $count = $stmt_c->get_result()->fetch_assoc()['total'];
    
    $reg_number = "$p_code/$year/" . str_pad($count + 1, $p_pad, "0", STR_PAD_LEFT);
    $hashed_pass = password_hash($reg_number, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (full_name, reg_number, email, password, role, department) VALUES (?, ?, ?, ?, 'student', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $full_name, $reg_number, $email, $hashed_pass, $dept);

    if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Use your SMTP provider
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noahchepkonga1@gmail.com'; // Your email
            $mail->Password   = 'sqki pcfh udva syiu';   // Your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'MKU Admissions');
            $mail->addAddress($email, $full_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Admission Confirmation - Portal Assistant AI';
            $mail->Body    = "
             <div style='font-family: Verdana, sans-serif; font-size:14px;'>
    <h2 style='color:#d63384;'>Welcome to Mount Kenya University, $fname! 😘💖</h2>
    
    <p style='line-height:1.6em;'>
        Your admission has been processed successfully! 💌✨<br>
        <span style='font-size:16px;'>🎉🌹💫🌸🎀💫🌹🎉</span>
    </p>
    
    <hr>
    
    <p><strong>Admission Number:</strong> $reg_number</p>
    <p><strong>Default Password:</strong> $reg_number</p>
    
    <hr>
    
    <p style='line-height:1.6em;'>
        Please change your password once you log in 🔑💖<br>
        Don’t forget to complete your unit registration ✨📚<br>
        <span style='font-size:16px;'>💓💞💖💋🥰💌💓💞</span>
    </p>
    
    <p style='margin-top:20px; font-size:15px;'>
        Sending you a little hug for your first day as a student 🤗❤️<br>
        <span style='font-size:18px;'>🌟💖🌹🔥💫💋</span>
    </p>
    
    <p>Best Regards,<br><strong>Registrar Academic</strong></p>
</div>
";

            $mail->send();
            $message = "<div class='alert success'><b>Success!</b> Student Registered and Notification Sent to $email. <br> Reg Number: $reg_number</div>";
        } catch (Exception $e) {
            $message = "<div class='alert success'><b>Success!</b> Student Registered ($reg_number), but email failed. Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $message = "<div class='alert error'>Error: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | Admin Portal</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --accent: #e0e7ff;
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        /* HEADER */
        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; }
        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 50px; border-radius: 8px; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; }
        .branding small { color: var(--text-light); display: block; font-size: 0.85rem; }

        /* MAIN NAV */
        nav { background: var(--primary); padding: 0 5%; }
        .nav-top { display: flex; gap: 10px; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; border-bottom: 3px solid transparent; }
        nav a:hover { color: white; background: rgba(255,255,255,0.1); }
        nav a.active { color: white; background: rgba(255,255,255,0.15); border-bottom: 3px solid white; }

        /* SUB NAV */
        .nav-sub { background: #f1f5f9; display: flex; gap: 10px; padding: 0 5%; border-bottom: 1px solid var(--border); }
        .nav-sub a { color: var(--text-light); font-size: 0.8rem; padding: 10px 15px; text-decoration: none; font-weight: 600; }
        .nav-sub a:hover { color: var(--primary); }
        .nav-sub .active { color: var(--primary); font-weight: 700; border-bottom: 2px solid var(--primary); }

        /* CONTENT */
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .admin-strip { background: var(--accent); padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; font-weight: 700; font-size: 0.85rem; color: var(--primary-dark); }
        
        .section-box { background: var(--white); border-radius: 12px; border: 1px solid var(--border); padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        /* Increased spacing and clearer grouping */
.form-grid { 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 30px; /* Increased from 20px to 30px for more horizontal space */
    margin-bottom: 25px; 
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 8px; /* Space between the Label and the Input box */
}

/* Ensure inputs look clean within their boxes */
input, select { 
    width: 100%; 
    padding: 12px 15px; 
    border: 1px solid var(--border); 
    border-radius: 8px; 
    font-size: 0.95rem; 
    background-color: #ffffff;
}
        .full-row { grid-column: span 3; }
        
        label { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-light); margin-bottom: 8px; text-transform: uppercase; }
        input, select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; transition: 0.2s; }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        .btn-reg { background: var(--primary); color: white; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; }
        .btn-reg:hover { background: var(--primary-dark); }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<header>
    <div class="branding">
        <img src="../Images/logo.jpg" class="logoimg" alt="Logo">
        <div>
            <h1>Student Support Agent – Admin</h1>
            <small>Academic Administration Portal</small>
        </div>
    </div>
</header>

<nav>
    <div class="nav-top">
        <a href="Admin-index.php">Dashboard</a>
        <a href="add_student.php" class="active">Manage Students</a>
        <a href="Admin-index.php?section=units">Manage Units</a>
        <a href="Admin-index.php?section=registrations">Registrations</a>
        <a href="../logout.php">Sign Out</a>
    </div>
</nav>

<div class="nav-sub">
    <a href="add_student.php" class="active">Add Student</a>
    <a href="Admin-index.php?section=students">Student Directory</a>
</div>

<div class="container">
    <div class="admin-strip">
        <span>Logged in: <?php echo htmlspecialchars($admin_name); ?></span>
        <span>Action: Manual Student Enrollment</span>
    </div>

    <?php echo $message; ?>

    <div class="section-box">
        <form action="" method="POST">
           <div class="form-grid">
    <div class="field-group">
        <label>First Name</label>
        <input type="text" name="fname" placeholder="e.g. Noah" required>
    </div>

    <div class="field-group">
        <label>Middle Name</label>
        <input type="text" name="mname" placeholder="Optional">
    </div>

    <div class="field-group">
        <label>Surname</label>
        <input type="text" name="lname" placeholder="e.g. Chepkonga" required>
    </div>



                <div class="full-row">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="student@mku.ac.ke" required>
                </div>

                <div class="full-row">
                    <label>Department</label>
                    <select name="department" required>
                        <option value="">-- Select Department --</option>
                        <option value="Information Technology">Department of Information Technology</option>
                        <option value="Computer Science">Department of Computer Science</option>
                        <option value="Enterprise Computing">Department of Enterprise Computing</option>
                        <option value="Information Science & Knowledge Management">Department of Information Science & Knowledge Management</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="add_student" class="btn-reg">REGISTER NEW STUDENT</button>
        </form>
    </div>
</div>

</body>
</html>