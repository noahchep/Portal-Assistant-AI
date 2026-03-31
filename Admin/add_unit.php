<?php
/* ==========================
   DATABASE CONNECTION
========================== */
include_once('db_connect.php');

if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
}

$message = "";

/* ==========================
   INSERT UNIT LOGIC
========================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_unit'])) {

    // 1. Collect and Sanitize (Added day_of_week)
    $unit_code      = mysqli_real_escape_string($conn, trim($_POST['unit_code']));
    $course_title   = mysqli_real_escape_string($conn, $_POST['course_title']);
    $day_of_week    = mysqli_real_escape_string($conn, $_POST['day_of_week']); // New Field
    $time_from      = mysqli_real_escape_string($conn, $_POST['time_from']);
    $time_to        = mysqli_real_escape_string($conn, $_POST['time_to']);
    $venue          = mysqli_real_escape_string($conn, $_POST['venue']);
    $unit_group     = mysqli_real_escape_string($conn, $_POST['unit_group']);
    $lecturer       = mysqli_real_escape_string($conn, $_POST['lecturer']);
    $exam_date      = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $semester       = mysqli_real_escape_string($conn, $_POST['semester']);
    $academic_year  = mysqli_real_escape_string($conn, $_POST['academic_year']);

    // 2. Updated SQL Statement
    $sql = "INSERT INTO timetable 
            (unit_code, course_title, day_of_week, time_from, time_to, venue, unit_group, 
             lecturer, exam_date, semester, academic_year) 
            VALUES 
            ('$unit_code','$course_title','$day_of_week','$time_from','$time_to','$venue',
             '$unit_group','$lecturer','$exam_date','$semester','$academic_year')";

    // 3. Execution & Error Handling
    if (mysqli_query($conn, $sql)) {
        $message = "success|Unit added successfully.";
    } else {
        if (mysqli_errno($conn) == 1062) {
            $message = "error|Unit code '$unit_code' already exists for this semester.";
        } else {
            $message = "error|Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<style>
    .form-container { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; font-family: sans-serif; }
    .msg-box { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; }
    .msg-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .full-width { grid-column: span 2; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #475569; font-size: 0.85rem; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
    .btn-save { background: #4f46e5; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.2s; }
    .btn-save:hover { background: #3730a3; }
</style>

<div class="form-container">
    <h2 style="margin-top:0; color: #1e293b; font-size: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        Add Unit to Current Timetable
    </h2>

    <?php if ($message): 
        $parts = explode('|', $message); ?>
        <div class="msg-box msg-<?php echo $parts[0]; ?>">
            <?php echo $parts[1]; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="Admin-index.php?section=add_unit" class="grid-form">
        
        <div class="form-group">
            <label>Unit Code</label>
            <input type="text" name="unit_code" required placeholder="e.g. BIT 2203">
        </div>

        <div class="form-group">
            <label>Course Title</label>
            <input type="text" name="course_title" required>
        </div>

        <div class="form-group">
            <label>Day of Week</label>
            <select name="day_of_week" required>
                <option value="">-- Select Day --</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>
        </div>

        <div class="form-group">
            <label>Venue</label>
            <input type="text" name="venue" placeholder="e.g. CC1">
        </div>

        <div class="form-group">
            <label>Time From</label>
            <input type="time" name="time_from" required>
        </div>

        <div class="form-group">
            <label>Time To</label>
            <input type="time" name="time_to" required>
        </div>

        <div class="form-group">
            <label>Unit Group</label>
            <input type="text" name="unit_group" placeholder="e.g. Group A">
        </div>

        <div class="form-group">
            <label>Lecturer</label>
            <input type="text" name="lecturer">
        </div>

        <div class="form-group">
            <label>Exam Date</label>
            <input type="date" name="exam_date">
        </div>

        <div class="form-group">
            <label>Semester</label>
            <select name="semester" required>
                <option value="">-- Select --</option>
                <option value="1">Jan – Apr</option>
                <option value="2">May – Aug</option>
                <option value="3">Sep – Dec</option>
            </select>
        </div>

        <div class="form-group">
            <label>Academic Year</label>
            <input type="text" name="academic_year" required placeholder="2026">
        </div>

        <div class="full-width">
            <button type="submit" name="save_unit" class="btn-save">Save Unit to Timetable</button>
        </div>
    </form>
</div>