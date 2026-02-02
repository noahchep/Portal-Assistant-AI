<?php
session_start();

/* ==========================
   ACCESS CONTROL
========================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ==========================
   DATABASE CONNECTION
========================== */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";

/* ==========================
   INSERT UNIT (COMPLETE & SECURE)
========================== */
if (isset($_POST['save_unit'])) {

    // 1. Collect and Sanitize all inputs from the form
    $unit_code      = mysqli_real_escape_string($conn, trim($_POST['unit_code']));
    $course_title   = mysqli_real_escape_string($conn, $_POST['course_title']);
    $time_from      = mysqli_real_escape_string($conn, $_POST['time_from']);
    $time_to        = mysqli_real_escape_string($conn, $_POST['time_to']);
    $venue          = mysqli_real_escape_string($conn, $_POST['venue']);
    $unit_group     = mysqli_real_escape_string($conn, $_POST['unit_group']);
    $lecturer       = mysqli_real_escape_string($conn, $_POST['lecturer']);
    $exam_date      = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $semester       = mysqli_real_escape_string($conn, $_POST['semester']);
    $academic_year  = mysqli_real_escape_string($conn, $_POST['academic_year']);

    // 2. Prepare the SQL Statement
    $sql = "INSERT INTO timetable
            (unit_code, course_title, time_from, time_to, venue, unit_group,
             lecturer, exam_date, semester, academic_year)
            VALUES
            ('$unit_code','$course_title','$time_from','$time_to','$venue',
             '$unit_group','$lecturer','$exam_date','$semester','$academic_year')";

    // 3. Execute and handle errors (including our new Unique constraint)
    if (mysqli_query($conn, $sql)) {
        $message = "Success: Unit added successfully.";
    } else {
        if (mysqli_errno($conn) == 1062) {
            $message = "Error: Unit code '$unit_code' already exists for this semester.";
        } else {
            $message = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Unit | Admin</title>

<style>
body {
    font-family: Verdana, sans-serif;
    font-size: 12px;
}
#content {
    width: 800px;
    margin: 30px auto;
    border: 1px solid #ccc;
    background: #fff;
}
.section-title {
    background: #f2f2f2;
    padding: 10px;
    font-weight: bold;
    color: #0056b3;
    border-bottom: 1px solid #ccc;
}
form {
    padding: 20px;
}
label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
}
input, select {
    width: 100%;
    padding: 6px;
    margin-top: 3px;
    font-size: 12px;
}
button {
    margin-top: 15px;
    padding: 8px 15px;
    background: #0056b3;
    color: #fff;
    border: none;
    font-weight: bold;
    cursor: pointer;
}
button:hover {
    background: #003f7f;
}
.success {
    background: #e6ffe6;
    border: 1px solid #28a745;
    padding: 10px;
    margin: 10px;
}
.error {
    background: #ffe6e6;
    border: 1px solid #dc3545;
    padding: 10px;
    margin: 10px;
}
</style>
</head>

<body>

<div id="content">

<div class="section-title">Add Unit Offered This Semester</div>

<?php if ($message): ?>
<div class="<?php echo (str_contains($message, 'success')) ? 'success' : 'error'; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<form method="post">

<label>Unit Code</label>
<input type="text" name="unit_code" required placeholder="e.g. BIT 2203">

<label>Course Title</label>
<input type="text" name="course_title" required>

<label>Time From</label>
<input type="time" name="time_from" required>

<label>Time To</label>
<input type="time" name="time_to" required>

<label>Venue</label>
<input type="text" name="venue">

<label>Unit Group</label>
<input type="text" name="unit_group" placeholder="Group A / Group B">

<label>Lecturer</label>
<input type="text" name="lecturer">

<label>Exam Date</label>
<input type="date" name="exam_date">

<label>Semester</label>
<select name="semester" required>
    <option value="">-- Select Semester --</option>
    <option value="Jan-Apr">Jan – Apr</option>
    <option value="May-Aug">May – Aug</option>
    <option value="Sep-Dec">Sep – Dec</option>
</select>

<label>Academic Year</label>
<input type="text" name="academic_year" required placeholder="2025/2026">

<button type="submit" name="save_unit">Save Unit</button>

</form>

</div>
</body>
</html>
