<?php
/* ==========================
   1. DATABASE & POST LOGIC
   ========================== */
include_once('db_connect.php');
if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "portal-asisstant-ai");
}

// Handle the Modal Form Submission
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

/* ==========================
   2. SEARCH & COUNT LOGIC
   ========================== */
$search = $_GET['search'] ?? '';
$year_filter = $_GET['year_level'] ?? 'all';

$query = "SELECT * FROM academic_workload WHERE 1=1";
if ($year_filter !== 'all') {
    $query .= " AND year_level = '" . mysqli_real_escape_string($conn, $year_filter) . "'";
}
if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $query .= " AND (unit_code LIKE '%$s%' OR unit_name LIKE '%$s%')";
}

$query .= " ORDER BY year_level ASC, semester_level ASC";
$result = mysqli_query($conn, $query);
$count = mysqli_num_rows($result); 
?>

<form class="search-container" method="GET" action="Admin-index.php" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <input type="hidden" name="section" value="units">
    <div style="flex-grow: 1;">
        <input type="text" name="search" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;" 
               placeholder="Search by Code or Unit Name..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
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
        <h2 style="margin: 0; font-size: 1.1rem; color: var(--primary);">Academic Year Workload (Master Plan)</h2>
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
                    <th style="padding: 12px; width: 160px; white-space: nowrap;">Offering Time</th>
                    <th style="padding: 12px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($count > 0) {
                    $i = 1; 
                    while($row = mysqli_fetch_assoc($result)) {
                        $offering = $row['offering_time'];
                        
                        // New Color Logic: Green (Every), Orange (Twice), Red (Once)
                        if ($offering == 'Every Semester') {
                            $badgeStyle = "background: #dcfce7; color: #166534;"; // Green
                        } elseif ($offering == 'Twice in 3 Semesters') {
                            $badgeStyle = "background: #ffedd5; color: #9a3412;"; // Orange/Peach
                        } else {
                            $badgeStyle = "background: #fee2e2; color: #991b1b;"; // Red
                        }

                        $semVal = ($row['semester_level'] == '1st Semester') ? '1' : (($row['semester_level'] == '2nd Semester') ? '2' : '3');
                        
                        echo "<tr style='border-bottom: 1px solid #f1f5f9;'>
                                <td style='padding: 12px; color: var(--text-light);'>$i</td>
                                <td style='padding: 12px;'><span style='background: var(--accent); color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;'>{$row['unit_code']}</span></td>
                                <td style='padding: 12px;'>{$row['unit_name']}</td>
                                <td style='padding: 12px;'>{$row['year_level']}</td>
                                <td style='padding: 12px;'>{$row['semester_level']}</td>
                                <td style='padding: 12px; white-space: nowrap;'>
                                    <span style='padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; display: inline-block; $badgeStyle'>
                                        $offering
                                    </span>
                                </td>
                                <td style='padding: 12px; text-align: center;'>
                                    <button onclick=\"openScheduleModal('{$row['unit_code']}', '" . addslashes($row['unit_name']) . "', '$semVal')\" 
                                            style='background: #4f46e5; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: bold;'>
                                        + Schedule Unit
                                    </button>
                                </td>
                              </tr>";
                        $i++; 
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 40px;'>No units found matching your criteria.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:15px; width:480px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <h3 id="modalTitle" style="margin-top:0; color:#4f46e5; font-size: 1.2rem;">Schedule Semester Unit</h3>
        <form method="POST">
            <input type="hidden" name="unit_code" id="form_unit_code">
            <input type="hidden" name="course_title" id="form_course_title">
            <input type="hidden" name="semester" id="form_semester">
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:bold; color: #475569; text-transform: uppercase; margin-bottom:5px;">Lecturer Name</label>
                <input type="text" name="lecturer" placeholder="e.g. Mrs. MATHENGE" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:bold; color: #475569; text-transform: uppercase; margin-bottom:5px;">Student Group / Class</label>
                <input type="text" name="unit_group" placeholder="e.g. Class 1 or Jan24" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; font-size:0.75rem; font-weight:bold; color: #475569; text-transform: uppercase; margin-bottom:5px;">Time From</label>
                    <input type="time" name="time_from" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:0.75rem; font-weight:bold; color: #475569; text-transform: uppercase; margin-bottom:5px;">Time To</label>
                    <input type="time" name="time_to" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
                </div>
            </div>
            <div style="margin-bottom:25px;">
                <label style="display:block; font-size:0.75rem; font-weight:bold; color: #475569; text-transform: uppercase; margin-bottom:5px;">Venue / Room</label>
                <input type="text" name="venue" placeholder="e.g. CC1 or MLT Hall B" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeModal()" style="background:#f1f5f9; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; color: #475569; font-weight: 600;">Cancel</button>
                <button type="submit" name="save_to_timetable" style="background:#4f46e5; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold;">Push to Timetable</button>
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