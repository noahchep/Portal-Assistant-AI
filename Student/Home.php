<?php
session_start();

// 1. Check if user_id exists (Are they logged in?)
// 2. Check if the role is 'student' (Are they allowed here?)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    
    // Optional: If they are an admin trying to sneak in, send them to their own dashboard
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php?error=access_denied");
    } else {
        // Otherwise, send to login
        header("Location: ../login.php");
    }
    exit();
}

/* --- 2. DB CONNECTION --- */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* --- 3. FETCH STUDENT DATA --- */
$user_id = $_SESSION['user_id'];
$student_reg = $_SESSION['reg_number'];
$student_dept = $_SESSION['department'];

$sql = "SELECT full_name, reg_number, password, department, created_at FROM users WHERE id = ? AND role = 'student'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) !== 1) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$student = mysqli_fetch_assoc($result);

// Determine student year level based on registration number or creation date
function getStudentYearLevel($reg_number, $created_at) {
    // Try to extract year from registration number (e.g., BIT/2024/43255)
    if (preg_match('/\/(\d{4})\//', $reg_number, $matches)) {
        $admission_year = intval($matches[1]);
        $current_year = date('Y');
        $year_diff = $current_year - $admission_year;
        
        if ($year_diff == 0) return 'First Year';
        if ($year_diff == 1) return 'Second Year';
        if ($year_diff == 2) return 'Third Year';
        if ($year_diff >= 3) return 'Fourth Year';
    }
    
    // Fallback: use created_at date
    $created_year = date('Y', strtotime($created_at));
    $current_year = date('Y');
    $year_diff = $current_year - $created_year;
    
    if ($year_diff == 0) return 'First Year';
    if ($year_diff == 1) return 'Second Year';
    if ($year_diff == 2) return 'Third Year';
    return 'Fourth Year';
}

$student_year_level = getStudentYearLevel($student['reg_number'], $student['created_at']);

// Security Check: Is the student still using the default password?
$is_default_password = password_verify($student['reg_number'], $student['password']);

$name_parts = explode(" ", $student['full_name']);
$fname = $name_parts[0] ?? 'Student';

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_assignment'])) {
        $assignment_id = intval($_POST['assignment_id']);
        $submission_text = mysqli_real_escape_string($conn, $_POST['submission_text']);
        
        // Handle file upload
        $file_path = null;
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
            $target_dir = "../uploads/submissions/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_name = time() . '_' . $student_reg . '_' . basename($_FILES['submission_file']['name']);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_file)) {
                $file_path = $file_name;
            }
        }
        
        $insert = "INSERT INTO assignment_submissions (assignment_id, student_reg, submission_text, file_path, submitted_at, status) 
                   VALUES ($assignment_id, '$student_reg', '$submission_text', " . ($file_path ? "'$file_path'" : "NULL") . ", NOW(), 'submitted')";
        if (mysqli_query($conn, $insert)) {
            echo '<div class="alert-success">✅ Assignment submitted successfully!</div>';
        } else {
            echo '<div class="alert-error">❌ Error submitting assignment: ' . $conn->error . '</div>';
        }
    }
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Get student's enrolled units (from registered_courses)
$student_units_query = "SELECT DISTINCT unit_code FROM registered_courses WHERE student_reg_no = '$student_reg' AND status = 'Confirmed'";
$student_units_result = mysqli_query($conn, $student_units_query);
$student_units = [];
while($unit = mysqli_fetch_assoc($student_units_result)) {
    $student_units[] = $unit['unit_code'];
}

$units_list = !empty($student_units) ? "'" . implode("','", $student_units) . "'" : "''";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Portal Assistant AI</title>
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
            --warning-bg: #fff7ed;
            --warning-text: #9a3412;
            --warning-border: #fdba74;
            --success: #10b981;
            --danger: #ef4444;
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 50px; border-radius: 8px; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; }
        .branding small { color: var(--text-light); display: block; font-size: 0.85rem; }

        nav { background: var(--primary); padding: 0 5%; display: flex; gap: 10px; flex-wrap: wrap; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; border-bottom: 3px solid transparent; }
        nav a:hover { color: white; background: rgba(255,255,255,0.1); }
        nav a.active { color: white; border-bottom: 3px solid white; background: rgba(255,255,255,0.15); }

        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        
        .student-strip { background: var(--accent); padding: 15px 25px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; color: var(--primary-dark); font-weight: 700; flex-wrap: wrap; gap: 10px; }

        .security-alert { background: var(--warning-bg); border: 1px solid var(--warning-border); color: var(--warning-text); padding: 15px 25px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .security-alert button { background: var(--warning-text); color: white; border: none; padding: 8px 15px; border-radius: 6px; font-weight: 600; cursor: pointer; }

        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-bottom: 30px; }
        
        .card { background: var(--white); border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
        .card-header { background: var(--primary); color: white; padding: 15px 20px; font-weight: 700; }
        .card-body { padding: 20px; max-height: 400px; overflow-y: auto; }
        
        .material-item, .assignment-item { border-bottom: 1px solid var(--border); padding: 15px 0; }
        .material-item:last-child, .assignment-item:last-child { border-bottom: none; }
        .material-title, .assignment-title { font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .material-meta, .assignment-meta { font-size: 0.75rem; color: var(--text-light); margin-bottom: 8px; }
        
        .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-sm { padding: 4px 10px; font-size: 0.75rem; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 800px; max-height: 85vh; overflow-y: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-family: inherit; }
        .form-group textarea { resize: vertical; }

        .submission-status { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
        .status-submitted { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fed7aa; color: #92400e; }
        .status-graded { background: #e0e7ff; color: #3730a3; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); }
        .data-table th { background: var(--bg); font-weight: 700; }

        footer { text-align: center; padding: 40px; color: var(--text-light); font-size: 0.85rem; }
        #chat-trigger { position: fixed; bottom: 30px; right: 30px; background: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4); z-index: 100; font-size: 1.5rem; }
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
</header>

<nav>
    <a href="home.php?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="home.php?page=materials" class="<?php echo $page === 'materials' ? 'active' : ''; ?>">📚 Course Materials</a>
    <a href="home.php?page=assignments" class="<?php echo $page === 'assignments' ? 'active' : ''; ?>">📝 Assignments</a>
    <a href="home.php?page=my_submissions" class="<?php echo $page === 'my_submissions' ? 'active' : ''; ?>">📋 My Submissions</a>
    <a href="personal_information.php">👤 Information Update</a>
    <a href="teaching_timetable.php">📅 Timetables</a>
    <a href="registration.php">📖 Course Registration</a>        
    <a href="../logout.php">🚪 Sign Out</a>
</nav>

<div class="container">
    <div class="student-strip">
        <span>Welcome back, <?php echo htmlspecialchars($fname); ?></span>
        <span><?php echo htmlspecialchars($student['reg_number']); ?> | <?php echo htmlspecialchars($student_dept); ?> Department | <?php echo $student_year_level; ?></span>
    </div>

    <?php if ($is_default_password): ?>
    <div id="securityAlertBox" class="security-alert">
        <span>⚠️ <strong>Security Notice:</strong> You are still using your registration number as your password. Change it now to protect your personal data.</span>
        <button onclick="window.location.href='change_password.php'">Change Password</button>
    </div>
    <?php endif; ?>

    <?php
    switch($page) {
        case 'dashboard': ?>
            <div class="dashboard-grid">
                <!-- Recent Materials Card -->
                <div class="card">
                    <div class="card-header">📚 Recent Learning Materials</div>
                    <div class="card-body">
                        <?php
                        if (!empty($student_units)) {
                            $recent_materials = mysqli_query($conn, "SELECT * FROM course_materials WHERE unit_code IN ($units_list) ORDER BY uploaded_at DESC LIMIT 5");
                            if (mysqli_num_rows($recent_materials) > 0):
                                while($material = mysqli_fetch_assoc($recent_materials)):
                        ?>
                            <div class="material-item">
                                <div class="material-title"><?php echo htmlspecialchars($material['title']); ?></div>
                                <div class="material-meta">Unit: <?php echo $material['unit_code']; ?> | Uploaded: <?php echo date('d M Y', strtotime($material['uploaded_at'])); ?></div>
                                <?php if ($material['material_type'] == 'youtube'): ?>
                                    <a href="<?php echo $material['youtube_url']; ?>" target="_blank" class="btn btn-primary btn-sm">▶️ Watch Tutorial</a>
                                <?php else: ?>
                                    <a href="../uploads/materials/<?php echo $material['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">📥 Download Material</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; else: ?>
                            <p>No materials uploaded yet.</p>
                        <?php endif;
                        } else { echo '<p>You are not registered for any units yet. Please complete course registration.</p>'; } ?>
                        <div style="margin-top: 15px;"><a href="home.php?page=materials" class="btn btn-primary">View All Materials →</a></div>
                    </div>
                </div>

                <!-- Pending Assignments Card -->
                <div class="card">
                    <div class="card-header">📝 Pending Assignments</div>
                    <div class="card-body">
                        <?php
                        if (!empty($student_units)) {
                            $pending_assignments = mysqli_query($conn, "SELECT a.* FROM assignments a WHERE a.unit_code IN ($units_list) AND a.due_date >= CURDATE() AND NOT EXISTS (SELECT 1 FROM assignment_submissions s WHERE s.assignment_id = a.id AND s.student_reg = '$student_reg') ORDER BY a.due_date ASC LIMIT 5");
                            if (mysqli_num_rows($pending_assignments) > 0):
                                while($assignment = mysqli_fetch_assoc($pending_assignments)):
                        ?>
                            <div class="assignment-item">
                                <div class="assignment-title"><?php echo htmlspecialchars($assignment['title']); ?></div>
                                <div class="assignment-meta">Due: <?php echo date('d M Y', strtotime($assignment['due_date'])); ?> | Marks: <?php echo $assignment['total_marks']; ?></div>
                                <button onclick="openAssignmentModal(<?php echo $assignment['id']; ?>, '<?php echo addslashes($assignment['title']); ?>')" class="btn btn-success btn-sm">Submit Assignment</button>
                            </div>
                        <?php endwhile; else: ?>
                            <p>No pending assignments. Great job!</p>
                        <?php endif;
                        } else { echo '<p>Complete course registration to see assignments.</p>'; } ?>
                        <div style="margin-top: 15px;"><a href="home.php?page=assignments" class="btn btn-primary">View All Assignments →</a></div>
                    </div>
                </div>
            </div>
        <?php break;

        case 'materials':
            ?>
            <div class="card">
                <div class="card-header">📚 All Course Materials</div>
                <div class="card-body">
                    <?php if (empty($student_units)): ?>
                        <p>You are not registered for any units yet. Please complete <a href="registration.php">course registration</a> first.</p>
                    <?php else: 
                        $materials_query = "SELECT * FROM course_materials WHERE unit_code IN ($units_list) ORDER BY uploaded_at DESC";
                        $materials_result = mysqli_query($conn, $materials_query);
                        if (mysqli_num_rows($materials_result) == 0): ?>
                            <p>No course materials available yet.</p>
                        <?php else: ?>
                            <?php while($material = mysqli_fetch_assoc($materials_result)): ?>
                                <div class="material-item">
                                    <div class="material-title"><?php echo htmlspecialchars($material['title']); ?></div>
                                    <div class="material-meta">Unit: <?php echo $material['unit_code']; ?> | Uploaded: <?php echo date('d M Y', strtotime($material['uploaded_at'])); ?></div>
                                    <div><?php echo nl2br(htmlspecialchars($material['description'])); ?></div>
                                    <?php if ($material['material_type'] == 'youtube'): ?>
                                        <div style="margin-top: 10px;">
                                            <iframe width="100%" height="200" src="https://www.youtube.com/embed/<?php echo $material['video_id']; ?>" frameborder="0" allowfullscreen></iframe>
                                        </div>
                                        <a href="<?php echo $material['youtube_url']; ?>" target="_blank" class="btn btn-primary btn-sm">🔗 Open on YouTube</a>
                                    <?php else: ?>
                                        <a href="../uploads/materials/<?php echo $material['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">📥 Download Material</a>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; 
                    endif; ?>
                </div>
            </div>
        <?php break;

        case 'assignments':
            ?>
            <div class="card">
                <div class="card-header">📝 All Assignments</div>
                <div class="card-body">
                    <?php if (empty($student_units)): ?>
                        <p>You are not registered for any units yet. Please complete <a href="registration.php">course registration</a> first.</p>
                    <?php else:
                        $assignments_query = "SELECT a.*, (SELECT COUNT(*) FROM assignment_submissions s WHERE s.assignment_id = a.id AND s.student_reg = '$student_reg') as submitted 
                                             FROM assignments a 
                                             WHERE a.unit_code IN ($units_list) 
                                             ORDER BY a.due_date ASC";
                        $assignments_result = mysqli_query($conn, $assignments_query);
                        if (mysqli_num_rows($assignments_result) == 0): ?>
                            <p>No assignments posted yet.</p>
                        <?php else: ?>
                            <?php while($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                                <div class="assignment-item">
                                    <div class="assignment-title"><?php echo htmlspecialchars($assignment['title']); ?></div>
                                    <div class="assignment-meta">
                                        Unit: <?php echo $assignment['unit_code']; ?> | 
                                        Due: <?php echo date('d M Y', strtotime($assignment['due_date'])); ?> | 
                                        Total Marks: <?php echo $assignment['total_marks']; ?>
                                        <?php if($assignment['submitted'] > 0): ?>
                                            <span class="submission-status status-submitted">✅ Submitted</span>
                                        <?php elseif(strtotime($assignment['due_date']) < time()): ?>
                                            <span class="submission-status status-pending">⏰ Late</span>
                                        <?php else: ?>
                                            <span class="submission-status status-pending">⏳ Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></div>
                                    <div style="margin-top: 10px;">
                                        <a href="../uploads/assignments/<?php echo $assignment['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">📥 Download Assignment</a>
                                        <?php if($assignment['submitted'] == 0): ?>
                                            <button onclick="openAssignmentModal(<?php echo $assignment['id']; ?>, '<?php echo addslashes($assignment['title']); ?>')" class="btn btn-success btn-sm">Submit Answer</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif;
                    endif; ?>
                </div>
            </div>
        <?php break;

        case 'my_submissions':
            $submissions_query = "SELECT s.*, a.title as assignment_title, a.unit_code, a.total_marks 
                                 FROM assignment_submissions s 
                                 JOIN assignments a ON s.assignment_id = a.id 
                                 WHERE s.student_reg = '$student_reg' 
                                 ORDER BY s.submitted_at DESC";
            $submissions_result = mysqli_query($conn, $submissions_query);
            ?>
            <div class="card">
                <div class="card-header">📋 My Assignment Submissions</div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($submissions_result) == 0): ?>
                        <p>You haven't submitted any assignments yet.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Assignment</th><th>Unit</th><th>Submitted On</th><th>Marks</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php while($sub = mysqli_fetch_assoc($submissions_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub['assignment_title']); ?></td>
                                        <td><?php echo $sub['unit_code']; ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($sub['submitted_at'])); ?></td>
                                        <td><?php echo $sub['obtained_marks'] ?? 'Pending'; ?> / <?php echo $sub['total_marks']; ?></td>
                                        <td>
                                            <?php if($sub['status'] == 'graded'): ?>
                                                <span class="submission-status status-submitted">✅ Graded</span>
                                            <?php else: ?>
                                                <span class="submission-status status-pending">⏳ Pending Review</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($sub['file_path']): ?>
                                                <a href="../uploads/submissions/<?php echo $sub['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">View</a>
                                            <?php else: ?>
                                                <span class="btn btn-sm" style="background:#e2e8f0;">Text Submission</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php break;

        default:
            // Default to dashboard
            header("Location: home.php?page=dashboard");
            break;
    }
    ?>
</div>

<!-- Assignment Submission Modal - Simple Textarea (No API Key Required) -->
<div id="assignmentModal" class="modal">
    <div class="modal-content">
        <h3 id="modalTitle">📝 Submit Assignment</h3>
        <form method="POST" enctype="multipart/form-data" id="submissionForm">
            <input type="hidden" name="assignment_id" id="assignment_id">
            <input type="hidden" name="submit_assignment" value="1">
            
            <div class="form-group">
                <label>Your Answer</label>
                <textarea name="submission_text" id="submission_text" rows="12" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; font-family: inherit; resize: vertical;" placeholder="Type your assignment answer here...&#10;&#10;You can:&#10;• Write detailed responses&#10;• Include code snippets&#10;• Explain concepts thoroughly&#10;&#10;You can also upload a file below if you prefer."></textarea>
                <small style="color: #666;">Write your answer directly in the box above.</small>
            </div>
            
            <div class="form-group">
                <label>Or Upload File (PDF, DOC, DOCX, ZIP)</label>
                <input type="file" name="submission_file" accept=".pdf,.doc,.docx,.zip">
                <small>Maximum file size: 10MB. You can either type your answer above OR upload a file.</small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-success">Submit Assignment</button>
                <button type="button" onclick="closeAssignmentModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="chat-trigger" onclick="toggleChat()">💬</div>

<footer>
    &copy; 2026 Mount Kenya University | Portal Assistant AI
</footer>

<script>
function openAssignmentModal(assignmentId, assignmentTitle) {
    document.getElementById('assignment_id').value = assignmentId;
    document.getElementById('modalTitle').innerHTML = '📝 Submit: ' + assignmentTitle;
    document.getElementById('assignmentModal').style.display = 'flex';
    // Clear previous content
    document.getElementById('submission_text').value = '';
}

function closeAssignmentModal() {
    document.getElementById('assignmentModal').style.display = 'none';
}

function toggleChat() {
    window.open('chatbot.php', '_blank', 'width=450,height=600,scrollbars=yes');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('assignmentModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>