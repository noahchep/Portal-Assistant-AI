<?php
session_start();

// 1. Check if user_id exists (Are they logged in?)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php?error=access_denied");
    } else {
        header("Location: ../login.php");
    }
    exit();
}

/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");

// 1. Get the Reg No from the Session
$student_reg_no = $_SESSION['reg_number'] ?? ''; 
$reg_number = $student_reg_no; 

// 2. Fetch the Student's real name and department from the users table
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT full_name, department, survey_done FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

$student_name = $user_data['full_name'] ?? 'Unknown Student';
// CAPTURING THE DEPARTMENT
$student_dept = $user_data['department'] ?? 'General';
$survey_done_status = $user_data['survey_done'] ?? 0;

$semester = "Jan/Apr";
$academic_year = "2026";

/* ===============================
    CORE LOGIC: REGISTRATION
================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_btn'])) {
    
    // Check current total (Confirmed + Provisional)
    $check_count_sql = "SELECT COUNT(*) as total FROM registered_courses WHERE student_reg_no = '$student_reg_no'";
    $count_res = mysqli_query($conn, $check_count_sql);
    $current_count_row = mysqli_fetch_assoc($count_res);
    $current_total = $current_count_row['total'];

    $max_allowed = 8;

    if ($current_total >= $max_allowed) {
        echo "<script>alert('Error: You have already registered the maximum limit of $max_allowed units.'); window.location='registration.php';</script>";
        exit();
    }

    $added_this_session = 0;

    for ($i = 1; $i <= 8; $i++) {
        if (!empty($_POST["courseCode$i"])) {
            
            // Check if limit is reached during the loop
            if (($current_total + $added_this_session) >= $max_allowed) {
                break; 
            }

            $unit_code   = mysqli_real_escape_string($conn, trim($_POST["courseCode$i"]));
            $exam_type   = mysqli_real_escape_string($conn, $_POST["examType$i"]);
            $class_group = mysqli_real_escape_string($conn, $_POST["classCode$i"]);

            // Check if the unit exists in the master timetable
            $check = mysqli_query($conn, "SELECT 1 FROM timetable WHERE unit_code='$unit_code' LIMIT 1");
            if (mysqli_num_rows($check) > 0) {
                $insert = mysqli_query($conn, "INSERT IGNORE INTO registered_courses 
                    (student_reg_no, unit_code, exam_type, class_group, semester, academic_year, status, department)
                    VALUES ('$student_reg_no','$unit_code','$exam_type','$class_group','$semester','$academic_year', 'Provisional', '$student_dept')");
                
                if ($insert && mysqli_affected_rows($conn) > 0) {
                    $added_this_session++;
                }
            }
        }
    }

    header("Location: registration.php?added=$added_this_session");
    exit();
}

/* ===============================
    CORE LOGIC: CONFIRM / DROP
================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['selected_units'])) {
    $units = $_POST['selected_units']; 

    // 1. REDIRECT TO SURVEY: Only if they click Confirm and haven't done the survey
    if (isset($_POST['btn_confirm_action']) && $survey_done_status == 0) {
        header("Location: survey.php");
        exit();
    }

    // 2. PROCESS ACTION: Run if survey is done OR if they are dropping units
    foreach ($units as $u_code) {
        $u_code = mysqli_real_escape_string($conn, $u_code);
        
        if (isset($_POST['btn_confirm_action'])) {
            mysqli_query($conn, "UPDATE registered_courses SET status='Confirmed' WHERE student_reg_no='$student_reg_no' AND unit_code='$u_code'");
        } elseif (isset($_POST['btn_drop_action'])) {
            mysqli_query($conn, "DELETE FROM registered_courses WHERE student_reg_no='$student_reg_no' AND unit_code='$u_code' AND status='Provisional'");
        }
    }

    header("Location: registration.php?updated=1");
    exit();
}

/* ===============================
    DATA FETCHING
================================ */
$confirmed = mysqli_query($conn, "SELECT rc.*, t.course_title FROM registered_courses rc JOIN timetable t ON rc.unit_code = t.unit_code WHERE rc.student_reg_no = '$student_reg_no' AND (rc.status = 'Confirmed' OR rc.status = 'Approved')");
$provisional = mysqli_query($conn, "SELECT rc.*, t.course_title FROM registered_courses rc JOIN timetable t ON rc.unit_code = t.unit_code WHERE rc.student_reg_no = '$student_reg_no' AND (rc.status = 'Provisional' OR rc.status IS NULL OR rc.status = '')");

// Fetch counts for the restriction display
$ui_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM registered_courses WHERE student_reg_no = '$student_reg_no'");
$ui_row = mysqli_fetch_assoc($ui_count_res);
$slots_used = $ui_row['total'];
$remaining = 8 - $slots_used;

if (!$confirmed || !$provisional) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Course Registration</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        header { background: white; border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; }
        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 50px; border-radius: 8px; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; }
        .branding small { color: var(--text-light); display: block; font-size: 0.85rem; }

        nav { background: var(--primary); padding: 0 5%; display: flex; gap: 10px; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; border-bottom: 3px solid transparent; }
        nav a:hover { color: white; background: rgba(255,255,255,0.1); }
        nav a.active { color: white; border-bottom: 3px solid white; background: rgba(255,255,255,0.15); }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .student-strip { background: #e0e7ff; padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; color: var(--primary-dark); font-weight: 700; font-size: 0.9rem; }

        .card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; border: 1px solid var(--border); }
        .card-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border); padding-bottom: 12px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f1f5f9; padding: 12px; font-size: 0.75rem; text-transform: uppercase; color: var(--text-light); }
        td { padding: 14px 12px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }

        .btn { padding: 10px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: 0.2s; display: inline-flex; align-items: center; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); }

        input[type="text"], select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; }

        .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
        .bg-success { background: #dcfce7; color: #166534; }
        .bg-warning { background: #fef3c7; color: #92400e; }

        #chat-fab { position: fixed; bottom: 30px; right: 30px; background: var(--primary); color: white; width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 10px 15px rgba(79, 70, 229, 0.4); z-index: 100; font-size: 1.5rem; }
        #chat-window { position: fixed; bottom: 100px; right: 30px; width: 350px; height: 500px; background: white; border-radius: 16px; display: none; flex-direction: column; box-shadow: 0 20px 25px rgba(0,0,0,0.1); border: 1px solid var(--border); z-index: 101; overflow: hidden; }
        #chat-content { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
        
        .msg { max-width: 85%; padding: 10px 14px; border-radius: 12px; font-size: 0.85rem; }
        .msg-bot { align-self: flex-start; background: #f1f5f9; border-left: 3px solid var(--primary); }
        .msg-user { align-self: flex-end; background: var(--primary); color: white; }

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
    <a href="#" class="active">Course Registration</a>
    <a href="../logout.php">Sign Out</a>
</nav>

<div class="container">
   <div class="student-strip">
    <span><?php echo htmlspecialchars($reg_number); ?> | <?php echo htmlspecialchars($student_name); ?></span>
    <span><?php echo htmlspecialchars($student_dept); ?></span>
</div>

    <?php if(isset($_GET['survey_complete'])): ?>
        <div class="card" style="background: #dcfce7; border-left: 5px solid #10b981; color: #166534; padding: 15px; margin-bottom: 20px;">
            🎉 <strong>Survey Recorded!</strong> You can now proceed to confirm your units.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title">✅ Confirmed Units</div>
        <table>
            <thead>
                <tr><th>#</th><th>Unit Code & Title</th><th>Exam Type</th><th>Group</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $i=1; while($row = mysqli_fetch_assoc($confirmed)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $row['unit_code']; ?></strong> - <?php echo $row['course_title']; ?></td>
                        <td><?php echo $row['exam_type']; ?></td>
                        <td><?php echo $row['class_group']; ?></td>
                        <td><span class="badge bg-success">Confirmed</span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <form action="" method="POST">
    <div class="card">
        <div class="card-title" style="color: var(--warning);">⏳ Provisional Units</div>
        <table>
            <thead>
                <tr><th width="40">Select</th><th>Unit Code & Title</th><th>Exam Type</th><th>Group</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($provisional)): ?>
                    <tr>
                        <td align="center"><input type="checkbox" name="selected_units[]" value="<?php echo $row['unit_code']; ?>"></td>
                        <td><strong><?php echo $row['unit_code']; ?></strong> - <?php echo $row['course_title']; ?></td>
                        <td><?php echo $row['exam_type']; ?></td>
                        <td><?php echo $row['class_group']; ?></td>
                        <td><span class="badge bg-warning">Pending</span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="submit" name="btn_drop_action" class="btn btn-outline" style="color:red;">Drop Selected</button>
            <button type="submit" name="btn_confirm_action" class="btn btn-success">Confirm Units</button>
        </div>
    </div>
    </form>

    <div class="card">
        <div class="card-title">
            ➕ Register New Courses 
            <span style="margin-left: auto; font-size: 0.8rem; color: <?php echo ($remaining <= 0) ? '#ef4444' : 'var(--text-light)'; ?>;">
                Units: <?php echo $slots_used; ?>/8 (<?php echo $remaining; ?> left)
            </span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="register_btn" value="1">
            <table>
                <thead>
                    <tr><th width="50">#</th><th>Unit Code</th><th>Exam Type</th><th>Class Group</th></tr>
                </thead>
                <tbody>
                    <?php for($k=1; $k<=6; $k++): ?>
                        <tr>
                            <td align="center"><?php echo $k; ?></td>
                            <td><input type="text" name="courseCode<?php echo $k; ?>" placeholder="e.g. BBT 1102" <?php if($remaining <= 0) echo 'disabled'; ?>></td>
                            <td>
                                <select name="examType<?php echo $k; ?>" <?php if($remaining <= 0) echo 'disabled'; ?>>
                                    <option>Regular</option><option>Retake</option>
                                </select>
                            </td>
                            <td>
                                <select name="classCode<?php echo $k; ?>" <?php if($remaining <= 0) echo 'disabled'; ?>>
                                    <option>Day</option><option>Evening</option>
                                </select>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <div style="text-align: center; margin-top: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 40px;" <?php if($remaining <= 0) echo 'disabled style="background:#94a3b8; cursor:not-allowed;"'; ?>>
                    <?php echo ($remaining <= 0) ? 'Limit Reached' : 'Submit Registration'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="chat-fab" onclick="toggleChat()">💬</div>
<div id="chat-window">
    <div style="background: var(--primary); color:white; padding: 15px; font-weight:bold; display: flex; justify-content: space-between; align-items: center;">
        <span>Portal Assistant</span>
        <span onclick="toggleChat()" style="cursor:pointer; font-size: 1.2rem;">&times;</span>
    </div>
    <div id="chat-content">
        <div class="msg msg-bot">
            Hello <?php echo explode(' ', $student_name)[0]; ?>! I'm your AI assistant. How can I help?
        </div>
    </div>
    <div style="padding: 12px; border-top: 1px solid var(--border); display: flex; gap: 8px;">
        <input type="text" id="chat-input" placeholder="Ask about units..." style="flex:1; border-radius: 20px; padding: 8px 15px; border: 1px solid var(--border);">
        <button onclick="sendChatMessage()" style="background: var(--primary); color: white; border-radius: 50%; border:none; width: 35px; height:35px;">➤</button>
    </div>
</div>

<script>
    function toggleChat() {
        const win = document.getElementById('chat-window');
        win.style.display = (win.style.display === 'flex') ? 'none' : 'flex';
    }

    async function sendChatMessage() {
        const input = document.getElementById('chat-input');
        const box = document.getElementById('chat-content');
        const msg = input.value.trim();
        if(!msg) return;

        box.innerHTML += `<div class="msg msg-user">${msg}</div>`;
        input.value = '';
        box.scrollTop = box.scrollHeight;

        try {
            const response = await fetch('chat_process_ml.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message=' + encodeURIComponent(msg)
            });
            const text = await response.text();
            box.innerHTML += `<div class="msg msg-bot">${text}</div>`;
            box.scrollTop = box.scrollHeight;
        } catch (e) {
            box.innerHTML += `<div class="msg msg-bot" style="color:red">Connection error.</div>`;
        }
    }
</script>

<footer>
    &copy; 2026 Mount Kenya University | Portal Assistant AI System
</footer>

</body>
</html>