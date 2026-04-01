<?php
/* ==========================
   1. DATABASE & POST LOGIC
   ========================== */
include_once('db_connect.php');
if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "portal-asisstant-ai");
}

$message = ""; // Initialize message variable

// Handle the Timetable Modal Submission
if (isset($_POST['save_to_timetable'])) {
    $u_code = mysqli_real_escape_string($conn, $_POST['unit_code']);
    $u_title = mysqli_real_escape_string($conn, $_POST['course_title']);
    $u_sem = mysqli_real_escape_string($conn, $_POST['semester']);
    $time_from = mysqli_real_escape_string($conn, $_POST['time_from']);
    $time_to = mysqli_real_escape_string($conn, $_POST['time_to']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $lecturer = mysqli_real_escape_string($conn, $_POST['lecturer']);
    $u_group = mysqli_real_escape_string($conn, $_POST['unit_group']); 
    $academic_year = date("Y");

    $insert_sql = "INSERT INTO timetable (unit_code, course_title, time_from, time_to, venue, unit_group, lecturer, semester, academic_year) 
                    VALUES ('$u_code', '$u_title', '$time_from', '$time_to', '$venue', '$u_group', '$lecturer', '$u_sem', '$academic_year')";
    
    if(mysqli_query($conn, $insert_sql)) {
        echo "<script>alert('Unit successfully scheduled for group $u_group!'); window.location='Admin-index.php?section=units';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Handle Add Student Logic
if (isset($_POST['full_name'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dept = mysqli_real_escape_string($conn, $_POST['department']);

    $dept_codes = [
        "Information Technology" => "BIT",
        "Information Science"    => "BIS",
        "Management"             => "BBM",
        "Economics"              => "BEC",
        "Accounting and Finance" => "BAF",
        "Community Health"       => "BCH",
        "Environmental Health"   => "BEH",
        "Nursing"                => "BSN",
        "Pharmacy"               => "BPH",
        "Medical School"         => "MBB",
        "Clinical Medicine"      => "BCM",
        "Educational Management" => "BED",
        "Educational Psychology" => "BEP",
        "Special Needs Education" => "BSN",
        "Energy Engineering"     => "BEE",
        "Electrical Engineering" => "BEL",
        "Natural Sciences"       => "BNS",
        "Animal Health"          => "BAH",
        "Psychology"             => "BPS",
        "Law"                    => "LLB",
        "Security Studies"       => "BSS",
        "Journalism"             => "BJM",
        "Hospitality Management" => "BHM",
        "Travel and Tourism"     => "BTT"
    ];

    $prefix = isset($dept_codes[$dept]) ? $dept_codes[$dept] : "STU";
    $year = date("Y");

    $query = "SELECT reg_number FROM users WHERE role = 'student' ORDER BY id DESC LIMIT 1";
    $result_reg = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result_reg) > 0) {
        $row_reg = mysqli_fetch_assoc($result_reg);
        $last_reg = $row_reg['reg_number'];
        $last_sequence = (int)substr($last_reg, -5);
        $new_sequence = str_pad($last_sequence + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $new_sequence = "43255"; 
    }
    
    $reg_number = "$prefix/$year/$new_sequence";
    $password_hashed = password_hash($reg_number, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (full_name, reg_number, email, department, role, password) 
            VALUES ('$full_name', '$reg_number', '$email', '$dept', 'student', '$password_hashed')";

    if (mysqli_query($conn, $sql)) {
        // PHPMailer logic would go here as per your original code
        $message = "success|Student registered! Reg No: $reg_number. (Email simulation successful)";
    } else {
        $message = "error|Database Error: " . mysqli_error($conn);
    }
}

/* ==========================
   2. SEARCH & FILTER LOGIC
   ========================== */
$search = $_GET['search'] ?? '';
$year_filter = $_GET['year_level'] ?? 'all';
$dept_filter = $_GET['department'] ?? 'all';

$query = "SELECT * FROM academic_workload WHERE 1=1";
if ($year_filter !== 'all') {
    $query .= " AND year_level = '" . mysqli_real_escape_string($conn, $year_filter) . "'";
}
if ($dept_filter !== 'all') {
    $query .= " AND department = '" . mysqli_real_escape_string($conn, $dept_filter) . "'";
}
if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $query .= " AND (unit_code LIKE '%$s%' OR unit_name LIKE '%$s%')";
}

$query .= " ORDER BY year_level ASC, semester_level ASC";
$result = mysqli_query($conn, $query);
$count = mysqli_num_rows($result); 
?>

<form class="search-container" method="GET" action="Admin-index.php" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex-wrap: wrap;">
    <input type="hidden" name="section" value="units">
    <div style="flex-grow: 1; min-width: 200px;">
        <input type="text" name="search" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;" 
               placeholder="Search by Code or Unit Name..." value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <select name="department" style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: white;">
        <option value="all">All Departments</option>
        <option value="Information Technology" <?php if($dept_filter == 'Information Technology') echo 'selected'; ?>>IT</option>
        <option value="Nursing" <?php if($dept_filter == 'Nursing') echo 'selected'; ?>>Nursing</option>
        </select>

    <select name="year_level" style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: white;">
        <option value="all">All Year Levels</option>
        <option value="First Year" <?php if($year_filter == 'First Year') echo 'selected'; ?>>First Year</option>
        <option value="Second Year" <?php if($year_filter == 'Second Year') echo 'selected'; ?>>Second Year</option>
        <option value="Third Year" <?php if($year_filter == 'Third Year') echo 'selected'; ?>>Third Year</option>
        <option value="Fourth Year" <?php if($year_filter == 'Fourth Year') echo 'selected'; ?>>Fourth Year</option>
    </select>
    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Filter</button>
</form>

<div class="section-box">
    <div style="padding: 15px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0; font-size: 1.1rem; color: var(--primary);">Academic Year Workload</h2>
        <span style="font-size: 0.8rem; color: var(--text-light);">Showing <strong><?php echo $count; ?></strong> Units</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 12px; width: 40px;">#</th>
                    <th style="padding: 12px;">Unit Code</th>
                    <th style="padding: 12px;">Unit Name</th>
                    <th style="padding: 12px;">Year Level</th>
                    <th style="padding: 12px;">Semester</th>
                    <th style="padding: 12px;">Offering Time</th>
                    <th style="padding: 12px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($count > 0) {
                    $i = 1; 
                    while($row = mysqli_fetch_assoc($result)) {
                        $offering = $row['offering_time'];
                        $badgeStyle = ($offering == 'Every Semester') ? "background: #dcfce7; color: #166534;" : "background: #fee2e2; color: #991b1b;";
                        $semVal = ($row['semester_level'] == '1st Semester') ? '1' : (($row['semester_level'] == '2nd Semester') ? '2' : '3');
                        
                        echo "<tr style='border-bottom: 1px solid #f1f5f9;'>
                                <td style='padding: 12px; color: var(--text-light);'>$i</td>
                                <td style='padding: 12px;'><span style='background: var(--accent); color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;'>{$row['unit_code']}</span></td>
                                <td style='padding: 12px;'>{$row['unit_name']}</td>
                                <td style='padding: 12px;'>{$row['year_level']}</td>
                                <td style='padding: 12px;'>{$row['semester_level']}</td>
                                <td style='padding: 12px;'>
                                    <span style='padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; $badgeStyle'>$offering</span>
                                </td>
                                <td style='padding: 12px; text-align: center;'>
                                    <button onclick=\"openScheduleModal('{$row['unit_code']}', '" . addslashes($row['unit_name']) . "', '$semVal')\" 
                                            style='background: #4f46e5; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: bold;'>
                                        + Schedule
                                    </button>
                                </td>
                              </tr>";
                        $i++; 
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:15px; width:480px;">
        <h3 id="modalTitle" style="margin-top:0; color:#4f46e5;">Schedule Unit</h3>
        <form method="POST">
            <input type="hidden" name="unit_code" id="form_unit_code">
            <input type="hidden" name="course_title" id="form_course_title">
            <input type="hidden" name="semester" id="form_semester">
            <div style="margin-bottom:15px;">
                <label>Lecturer</label>
                <input type="text" name="lecturer" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>Group</label>
                <input type="text" name="unit_group" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div style="display:flex; gap:10px;">
                <input type="time" name="time_from" required style="flex:1; padding:10px;">
                <input type="time" name="time_to" required style="flex:1; padding:10px;">
            </div>
            <div style="margin: 15px 0;">
                <label>Venue</label>
                <input type="text" name="venue" required style="width:100%; padding:10px;">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeModal()">Cancel</button>
                <button type="submit" name="save_to_timetable" style="background:#4f46e5; color:white; padding:10px 20px; border-radius:8px;">Push to Timetable</button>
            </div>
        </form>
    </div>
</div>

<script>
function openScheduleModal(code, title, sem) {
    document.getElementById('scheduleModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = "Schedule: " + code;
    document.getElementById('form_unit_code').value = code;
    document.getElementById('form_course_title').value = title;
    document.getElementById('form_semester').value = sem;
}
function closeModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}
</script>