<?php
// 1. Only start session if one doesn't exist (prevents the Notice)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================
   DATABASE CONNECTION
========================== */
include_once('db_connect.php');
if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
}

$message = "";

/* ==========================
   INSERT STUDENT LOGIC
========================== */
if (isset($_POST['add_student'])) {
    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $reg_number = mysqli_real_escape_string($conn, $_POST['reg_number']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $dept       = mysqli_real_escape_string($conn, $_POST['department']);
    $password   = password_hash($_POST['reg_number'], PASSWORD_DEFAULT); // Default password is Reg No

    $sql = "INSERT INTO users (full_name, reg_number, email, department, role, password) 
            VALUES ('$full_name', '$reg_number', '$email', '$dept', 'student', '$password')";

    if (mysqli_query($conn, $sql)) {
        $message = "success|Student $full_name registered successfully!";
    } else {
        $message = "error|Registration failed: " . mysqli_error($conn);
    }
}
?>

<div class="section-box">
    <h2 style="margin-top: 0; color: var(--primary); font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
        Register New Student
    </h2>

    <?php if ($message): 
        $parts = explode('|', $message); ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: 600; 
             background: <?php echo $parts[0] == 'success' ? '#dcfce7' : '#fee2e2'; ?>; 
             color: <?php echo $parts[0] == 'success' ? '#166534' : '#991b1b'; ?>;">
            <?php echo $parts[1]; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="Admin-index.php?section=add_student" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-group">
            <label style="display:block; font-weight:700; margin-bottom:5px;">Full Name</label>
            <input type="text" name="full_name" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
        </div>

        <div class="form-group">
            <label style="display:block; font-weight:700; margin-bottom:5px;">Registration Number</label>
            <input type="text" name="reg_number" required placeholder="e.g. BIT/001/2024" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
        </div>

        <div class="form-group">
            <label style="display:block; font-weight:700; margin-bottom:5px;">Email Address</label>
            <input type="email" name="email" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
        </div>

        <div class="form-group">
            <label style="display:block; font-weight:700; margin-bottom:5px;">Department</label>
            <select name="department" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                <option value="">-- Select Department --</option>
                <option value="Information Technology">Information Technology</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Enterprise Computing">Enterprise Computing</option>
                <option value="Information Science">Information Science</option>
            </select>
        </div>

        <div style="grid-column: span 2;">
            <button type="submit" name="add_student" style="background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 700;">
                Confirm & Register Student
            </button>
        </div>
    </form>
</div>