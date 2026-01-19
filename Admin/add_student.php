<?php
session_start();

/* SECURITY CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* DB CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Database connection failed");
}

/* INIT MESSAGE */
$message = "";

/* HANDLE FORM */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_student'])) {

    $reg   = mysqli_real_escape_string($conn, $_POST['reg_number']);
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $full_name = trim("$fname $mname $lname");

    // Default password = 123456 (hashed)
    $password = password_hash('123456', PASSWORD_DEFAULT);

    $sql = "INSERT INTO users 
            (full_name, reg_number, email, password, role) 
            VALUES 
            ('$full_name', '$reg', '$email', '$password', 'student')";

    if (mysqli_query($conn, $sql)) {
        $message = "<div style='color:green; font-weight:bold;'>Student Registered Successfully! Default password is <b>123456</b></div>";
    } else {
        $message = "<div style='color:red; font-weight:bold;'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MKU Admin : Add New Student</title>
    <style>
        /* Base Portal Styles */
        body { font-family: Verdana, sans-serif; font-size: 12px; background-color: #f2f2f2; margin: 0; }
        #content { width: 1000px; margin: 10px auto; background: #fff; border: 1px solid #aaa; border-radius: 20px; overflow: hidden; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
        
        #top_info { padding: 15px; border-bottom: 3px solid #0056b3; }
        .logoimg { max-height: 70px; }
        h1 { margin: 0; font-size: 22px; color: #0056b3; }

        /* Navigation */
        #navigation { background: #004080; }
        .ult-section { list-style: none; margin: 0; padding: 0; display: flex; }
        .ult-section li a { display: block; padding: 12px 20px; text-decoration: none; color: #fff; font-weight: bold; border-right: 1px solid #003366; }
        .ult-section li a:hover { background: #0056b3; }
        .active { background: #fff !important; }
        .active a { color: #0056b3 !important; }

        .left_articles { padding: 25px; }
        fieldset { border: 1px solid #0056b3; border-radius: 8px; padding: 20px; background: #fdfdfd; }
        legend { color: #0056b3; font-weight: bold; padding: 0 10px; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; font-size: 12px; }
        .dentry_label { background: #f2f2f2; font-weight: bold; width: 180px; color: #333; border: 1px solid #ddd; }
        
        input[type="text"], input[type="email"], select { width: 95%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { background: #28a745; color: white; padding: 10px 25px; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; font-size: 13px; }
        .btn-submit:hover { background: #218838; }
        .required { color: red; }
        .note-box { background: #ffffcc; padding: 10px; border: 1px solid #e6db55; font-size: 11px; margin-top: 15px; border-radius: 4px; }
    </style>
</head>
<body>

<div id="content">
    <div id="top_info">
        <table border="0" width="100%">
            <tr>
                 <td width="80"><img src="../Images/logo.jpg" class="logoimg" alt="MKU Logo" /></td>
                <td>
                    <h1>Administrative Portal</h1>
                    <small>Mount Kenya University - Student Management System</small>
                </td>
            </tr>
        </table>
    </div>

    <div id="navigation">
        <ul class="ult-section">
            <li><a href="admin_home.php">Dashboard</a></li>
            <li class="active"><a href="add_student.php">Add Student</a></li>
            <li><a href="#">View All Students</a></li>
            <li><a href="logout.php">Sign Out</a></li>
        </ul>
    </div>

    <div class="left_articles">
        <?php echo $message; ?>

        <fieldset>
            <legend>Register New Student</legend>
            <form action="add_student.php" method="post">
                <table>
                    <tr>
                        <td class="dentry_label">Registration Number : <span class="required">*</span></td>
                        <td colspan="3"><input type="text" name="reg_number" placeholder="e.g. BIT/2024/0000" required></td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Full Names : <span class="required">*</span></td>
                        <td><input type="text" name="fname" placeholder="First Name" required></td>
                        <td><input type="text" name="mname" placeholder="Middle Name"></td>
                        <td><input type="text" name="lname" placeholder="Surname" required></td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Official Email : <span class="required">*</span></td>
                        <td colspan="3"><input type="email" name="email" placeholder="student@mku.ac.ke" required></td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Phone Number : <span class="required">*</span></td>
                        <td><input type="text" name="phone" placeholder="07..." required></td>
                        <td class="dentry_label">Campus :</td>
                        <td>
                            <select name="campus">
                                <option>Main Campus (Thika)</option>
                                <option>Nairobi Campus</option>
                                <option>Mombasa Campus</option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <div style="text-align: center; margin-top: 20px;">
                    <input type="submit" name="add_student" class="btn-submit" value="REGISTER STUDENT">
                </div>

                <div class="note-box">
                    <b>System Note:</b> Upon registration, the student's default password will be their <b>Registration Number</b>. They will be required to change it on their first login.
                </div>
            </form>
        </fieldset>
    </div>

    <div id="footer">
        <p>&copy; 2026 Mount Kenya University | Admin Management System</p>
    </div>
</div>

</body>
</html>