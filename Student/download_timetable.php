<?php
session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "portal-asisstant-ai");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// Get parameters
$token = $_GET['token'] ?? '';
$student_reg = $_GET['student'] ?? '';

// Verify token (basic security)
if (empty($token) || empty($student_reg)) {
    die("Invalid request. Please go back and try again.");
}

// Get student info
$query = "SELECT full_name, reg_number, email, department FROM users WHERE reg_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_reg);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Get student's year level
function getStudentYearLevelFromReg($conn, $student_reg) {
    $query = "SELECT DISTINCT t.year_level 
              FROM registered_courses rc 
              JOIN timetable t ON rc.unit_code = t.unit_code 
              WHERE rc.student_reg_no = ? 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_reg);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['year_level'];
    }
    return 'First Year';
}

function getCurrentSemester() {
    $current_month = date('n');
    return ($current_month >= 1 && $current_month <= 6) ? 1 : 2;
}

function getSemesterName($semester_num) {
    return ($semester_num == 1) ? '1st Semester' : '2nd Semester';
}

function getStudentTimetableData($conn, $year_level, $semester_num) {
    $query = "SELECT unit_code, course_title, day_of_week, time_from, time_to, venue, lecturer 
              FROM timetable 
              WHERE year_level = ? AND semester = ? 
              ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), time_from";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $year_level, $semester_num);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $timetable = [];
    while ($row = $result->fetch_assoc()) {
        $timetable[] = $row;
    }
    return $timetable;
}

$student_year = getStudentYearLevelFromReg($conn, $student_reg);
$current_semester_num = getCurrentSemester();
$semester_name = getSemesterName($current_semester_num);
$timetable = getStudentTimetableData($conn, $student_year, $current_semester_num);

// Set headers for PDF download
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="timetable_' . $student['reg_number'] . '.html"');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Timetable - <?php echo $student['full_name']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .timetable th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .timetable td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .timetable tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .day {
            font-weight: bold;
            background-color: #e8f5e9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Academic Timetable</h1>
        <p><strong><?php echo $student['full_name']; ?></strong> | Reg No: <?php echo $student['reg_number']; ?></p>
        <p><?php echo $student_year; ?> - <?php echo $semester_name; ?> | Academic Year: <?php echo date('Y'); ?></p>
        <p>Generated on: <?php echo date('F j, Y g:i A'); ?></p>
    </div>

    <?php if (!empty($timetable)): ?>
    <table class="timetable">
        <thead>
            <tr>
                <th>#</th>
                <th>Unit Code</th>
                <th>Course Title</th>
                <th>Day</th>
                <th>Time</th>
                <th>Venue</th>
                <th>Lecturer</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; foreach ($timetable as $unit): ?>
            <tr>
                <td><?php echo $counter++; ?></td>
                <td><strong><?php echo $unit['unit_code']; ?></strong></td>
                <td><?php echo $unit['course_title']; ?></td>
                <td class="day"><?php echo $unit['day_of_week'] ?? 'TBA'; ?></td>
                <td><?php echo ($unit['time_from'] ?? 'TBA') . ' - ' . ($unit['time_to'] ?? 'TBA'); ?></td>
                <td><?php echo $unit['venue'] ?? 'TBA'; ?></td>
                <td><?php echo $unit['lecturer'] ?? 'TBA'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>✅ This is an official timetable. Please report any discrepancies to the academic office.</p>
        <p>📚 Total Units: <?php echo count($timetable); ?> | <?php echo $student_year; ?> - <?php echo $semester_name; ?></p>
        <p>© <?php echo date('Y'); ?> Academic Portal</p>
    </div>
    <?php else: ?>
    <p style="text-align: center; color: #ff6600;">⚠️ No timetable found. Please contact the academic office.</p>
    <?php endif; ?>
</body>
</html>
<?php
$conn->close();
?>