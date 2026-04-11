<?php
session_start();

/* ==========================
   ACCESS CONTROL
========================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

$lecturer_name = $_SESSION['user_name'];
$lecturer_dept = $_SESSION['department'];
$lecturer_reg = $_SESSION['reg_number'];
$lecturer_id = $_SESSION['user_id'];

/* ==========================
   DATABASE CONNECTION
========================== */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if password needs to be changed
$password_change_required = false;
$query = "SELECT password_changed FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data || !isset($user_data['password_changed']) || $user_data['password_changed'] == 0) {
    $password_change_required = true;
}

// Handle file upload for learning materials
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_material'])) {
        $unit_code = mysqli_real_escape_string($conn, $_POST['unit_code']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        $target_dir = "../uploads/materials/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["material_file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'png'];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file)) {
                $insert = "INSERT INTO course_materials (unit_code, title, description, file_path, file_type, uploaded_by, uploaded_at) 
                          VALUES ('$unit_code', '$title', '$description', '$file_name', '$file_type', '$lecturer_name', NOW())";
                if (mysqli_query($conn, $insert)) {
                    echo '<div class="alert-success">✅ Learning material uploaded successfully!</div>';
                }
            } else {
                echo '<div class="alert-error">❌ Error uploading file!</div>';
            }
        } else {
            echo '<div class="alert-error">❌ Invalid file type! Allowed: PDF, DOC, PPT, TXT, JPG, PNG</div>';
        }
    }
    
    // Handle YouTube link upload
    if (isset($_POST['upload_youtube'])) {
        $unit_code = mysqli_real_escape_string($conn, $_POST['unit_code']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $youtube_url = mysqli_real_escape_string($conn, $_POST['youtube_url']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $youtube_url, $matches);
        $video_id = $matches[1] ?? '';
        
        $insert = "INSERT INTO course_materials (unit_code, title, description, youtube_url, video_id, material_type, uploaded_by, uploaded_at) 
                  VALUES ('$unit_code', '$title', '$description', '$youtube_url', '$video_id', 'youtube', '$lecturer_name', NOW())";
        if (mysqli_query($conn, $insert)) {
            echo '<div class="alert-success">✅ YouTube tutorial added successfully!</div>';
        } else {
            echo '<div class="alert-error">❌ Error adding YouTube link!</div>';
        }
    }
    
    // Handle assignment upload
    if (isset($_POST['upload_assignment'])) {
        $unit_code = mysqli_real_escape_string($conn, $_POST['unit_code']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
        $assessment_type = mysqli_real_escape_string($conn, $_POST['assessment_type']);
        
        if ($assessment_type == 'CAT') {
            $total_marks = 20;
        } elseif ($assessment_type == 'Exam') {
            $total_marks = 70;
        } else {
            $total_marks = 10;
        }
        
        $target_dir = "../uploads/assignments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["assignment_file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['pdf', 'doc', 'docx', 'zip'];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
                $insert = "INSERT INTO assignments (unit_code, title, description, file_path, due_date, total_marks, assessment_type, created_by, created_at) 
                          VALUES ('$unit_code', '$title', '$description', '$file_name', '$due_date', '$total_marks', '$assessment_type', '$lecturer_name', NOW())";
                if (mysqli_query($conn, $insert)) {
                    echo '<div class="alert-success">✅ ' . $assessment_type . ' uploaded successfully! (' . $total_marks . ' marks)</div>';
                } else {
                    echo '<div class="alert-error">❌ Error: ' . $conn->error . '</div>';
                }
            } else {
                echo '<div class="alert-error">❌ Error uploading assignment!</div>';
            }
        } else {
            echo '<div class="alert-error">❌ Invalid file type! Allowed: PDF, DOC, DOCX, ZIP</div>';
        }
    }
    
    // Handle delete material
    if (isset($_POST['delete_material'])) {
        $material_id = intval($_POST['material_id']);
        mysqli_query($conn, "DELETE FROM course_materials WHERE id = $material_id");
        echo '<div class="alert-success">✅ Material deleted successfully!</div>';
    }
    
    // Handle delete assignment
    if (isset($_POST['delete_assignment'])) {
        $assignment_id = intval($_POST['assignment_id']);
        mysqli_query($conn, "DELETE FROM assignments WHERE id = $assignment_id");
        echo '<div class="alert-success">✅ Assignment deleted successfully!</div>';
    }
    
    // Handle grading submission
    if (isset($_POST['grade_submission'])) {
        $submission_id = intval($_POST['submission_id']);
        $obtained_marks = floatval($_POST['obtained_marks']);
        $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
        
        $update = "UPDATE assignment_submissions 
                   SET obtained_marks = $obtained_marks, 
                       feedback = '$feedback', 
                       status = 'graded' 
                   WHERE id = $submission_id";
        
        if (mysqli_query($conn, $update)) {
            echo '<div class="alert-success">✅ Marks awarded successfully!</div>';
        } else {
            echo '<div class="alert-error">❌ Error awarding marks: ' . $conn->error . '</div>';
        }
    }
    
    // Handle manual marks submission
    if (isset($_POST['save_manual_marks'])) {
        $student_reg = mysqli_real_escape_string($conn, $_POST['student_reg']);
        $unit_code = mysqli_real_escape_string($conn, $_POST['unit_code']);
        $assessment_type = mysqli_real_escape_string($conn, $_POST['assessment_type']);
        $assessment_name = mysqli_real_escape_string($conn, $_POST['assessment_name']);
        $obtained_marks = floatval($_POST['obtained_marks']);
        
        if ($assessment_type == 'CAT') {
            $total_marks = 20;
        } elseif ($assessment_type == 'Exam') {
            $total_marks = 70;
        } else {
            $total_marks = 10;
        }
        
        $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
        
        $insert = "INSERT INTO assignment_submissions (assignment_id, unit_code, student_reg, submission_text, obtained_marks, total_marks, feedback, submitted_at, status) 
                   VALUES (0, '$unit_code', '$student_reg', '[Manual Entry: $assessment_name - $unit_code]', $obtained_marks, $total_marks, '$feedback', NOW(), 'graded')";
        
        if (mysqli_query($conn, $insert)) {
            echo '<div class="alert-success">✅ Manual ' . $assessment_type . ' marks saved for ' . htmlspecialchars($student_reg) . '! (' . $obtained_marks . '/' . $total_marks . ')</div>';
        } else {
            echo '<div class="alert-error">❌ Error saving marks: ' . $conn->error . '</div>';
        }
    }
}

// Get statistics
$dept_students = 0;
$q1 = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student' AND department='$lecturer_dept'");
if ($q1) { $dept_students = mysqli_fetch_assoc($q1)['count']; }

$assigned_units_count = 0;
$q_assigned = mysqli_query($conn, "SELECT COUNT(*) as count FROM timetable WHERE lecturer = '$lecturer_name'");
if ($q_assigned) { $assigned_units_count = mysqli_fetch_assoc($q_assigned)['count']; }

$pending_registrations = 0;
$q3 = mysqli_query($conn, "SELECT COUNT(*) as count FROM registered_courses rc 
                           JOIN academic_workload aw ON rc.unit_code = aw.unit_code 
                           WHERE aw.department='$lecturer_dept' AND rc.status='Provisional'");
if ($q3) { $pending_registrations = mysqli_fetch_assoc($q3)['count']; }

// Get current section
$section = $_GET['section'] ?? 'dashboard';

// Get filter parameters
$submission_filter_status = $_GET['submission_status'] ?? '';
$submission_filter_type = $_GET['submission_type'] ?? '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $pass_query = "SELECT password FROM users WHERE id = ?";
    $pass_stmt = mysqli_prepare($conn, $pass_query);
    mysqli_stmt_bind_param($pass_stmt, "i", $lecturer_id);
    mysqli_stmt_execute($pass_stmt);
    $pass_result = mysqli_stmt_get_result($pass_stmt);
    $user_pass = mysqli_fetch_assoc($pass_result);
    
    if (password_verify($current_password, $user_pass['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ?, password_changed = 1 WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $lecturer_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $password_change_required = false;
                    echo '<div class="alert-success">✅ Password changed successfully! Please login again to continue.</div>';
                    echo '<meta http-equiv="refresh" content="2;url=../logout.php">';
                } else {
                    echo '<div class="alert-error">❌ Error updating password. Please try again.</div>';
                }
            } else {
                echo '<div class="alert-error">❌ New password must be at least 6 characters long!</div>';
            }
        } else {
            echo '<div class="alert-error">❌ New password and confirm password do not match!</div>';
        }
    } else {
        echo '<div class="alert-error">❌ Current password is incorrect!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard | Portal Assistant AI</title>
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
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text-main); }

        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
        .branding h1 { font-size: 1.3rem; color: var(--primary); }
        .user-info { display: flex; align-items: center; gap: 20px; }
        .user-badge { background: var(--accent); padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: var(--primary); }
        .logout-btn { background: var(--danger); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; }

        nav { background: var(--primary); padding: 0 5%; }
        .nav-top { display: flex; gap: 10px; flex-wrap: wrap; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; }
        nav a:hover, nav a.active { color: white; background: rgba(255,255,255,0.1); }

        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 2rem; color: var(--primary); margin-bottom: 5px; }
        .stat-card p { color: var(--text-light); font-size: 0.85rem; }

        .section-box { background: var(--white); border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 30px; }
        .section-box h2, .section-box h3 { margin-bottom: 20px; color: var(--text-main); }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); }
        .data-table th { background: var(--bg); font-weight: 700; }
        .data-table tr:hover { background: var(--accent); }

        .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: white; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-info { background: var(--info); color: white; }
        .btn-sm { padding: 4px 10px; font-size: 0.75rem; }

        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; }

        .password-banner { background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); border-left: 4px solid #f59e0b; padding: 20px; margin-bottom: 30px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .change-password-btn { background: #f59e0b; color: white; padding: 10px 24px; border-radius: 8px; border: none; cursor: pointer; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 600px; max-height: 85vh; overflow-y: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; }
        .form-group textarea { min-height: 80px; }

        .material-card { background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .material-title { font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .youtube-preview { margin-top: 10px; }
        .youtube-preview iframe { width: 100%; max-width: 300px; height: 170px; border-radius: 8px; }

        .no-data { text-align: center; padding: 40px; color: #666; background: #f9fafb; border-radius: 8px; }
        .filter-bar { margin-bottom: 20px; padding: 15px; background: #f1f5f9; border-radius: 8px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 0.7rem; font-weight: 600; color: var(--text-light); }
        .filter-group select, .filter-group input { padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem; min-width: 150px; }
        .filter-group button { padding: 8px 16px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .filter-group a { padding: 8px 16px; background: #64748b; color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; text-align: center; }
        
        .marks-awarded { font-weight: 700; color: var(--success); }
        .marks-pending { color: var(--warning); }
        
        .type-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
        .type-cat { background: #fef3c7; color: #92400e; }
        .type-assignment { background: #d1fae5; color: #065f46; }
        .type-exam { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<header>
    <div class="branding">
        <h1>📚 Lecturer Portal - <?php echo htmlspecialchars($lecturer_dept); ?> Department</h1>
    </div>
    <div class="user-info">
        <span class="user-badge">👨‍🏫 <?php echo htmlspecialchars($lecturer_name); ?></span>
        <a href="../logout.php" class="logout-btn">Sign Out</a>
    </div>
</header>

<nav>
    <div class="nav-top">
        <a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
        <a href="?section=my_courses" class="<?php echo $section === 'my_courses' ? 'active' : ''; ?>">My Courses</a>
        <a href="?section=materials" class="<?php echo $section === 'materials' ? 'active' : ''; ?>">Course Materials</a>
        <a href="?section=assignments" class="<?php echo $section === 'assignments' ? 'active' : ''; ?>">Assignments</a>
        <a href="?section=submissions" class="<?php echo $section === 'submissions' ? 'active' : ''; ?>">📋 Submissions</a>
        <a href="?section=students" class="<?php echo $section === 'students' ? 'active' : ''; ?>">My Students</a>
        <a href="?section=timetable" class="<?php echo $section === 'timetable' ? 'active' : ''; ?>">My Timetable</a>
    </div>
</nav>

<div class="container">
    <?php if ($password_change_required): ?>
    <div class="password-banner">
        <div class="password-banner-content">
            <h3>🔐 Password Change Required</h3>
            <p>For security reasons, you must change your temporary password.</p>
        </div>
        <button onclick="openPasswordModal()" class="change-password-btn">Change Password Now</button>
    </div>
    <?php endif; ?>

    <?php
    switch($section) {
        case 'dashboard': ?>
            <div class="dashboard-grid">
                <div class="stat-card"><h3><?php echo $dept_students; ?></h3><p>Students in Department</p></div>
                <div class="stat-card"><h3><?php echo $assigned_units_count; ?></h3><p>Units Assigned to You</p></div>
                <div class="stat-card"><h3 style="color: var(--warning);"><?php echo $pending_registrations; ?></h3><p>Pending Approvals</p></div>
            </div>
            <div class="section-box">
                <h3>📢 Welcome, <?php echo htmlspecialchars($lecturer_name); ?>!</h3>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>✅ Upload learning materials (PDF, PPT, DOC)</li>
                    <li>✅ Share YouTube tutorial links</li>
                    <li>✅ Create and upload assignments/CATs/Exams</li>
                    <li>✅ Review and grade student submissions</li>
                    <li>✅ Add manual marks for physical submissions</li>
                    <li>✅ Review student registrations</li>
                </ul>
            </div>
        <?php break;

        case 'my_courses':
            $courses_query = "SELECT t.*, aw.unit_name, aw.year_level, aw.semester_level, aw.offering_time 
                             FROM timetable t 
                             JOIN academic_workload aw ON t.unit_code = aw.unit_code 
                             WHERE t.lecturer = '$lecturer_name'
                             ORDER BY aw.year_level, aw.semester_level";
            $courses_result = mysqli_query($conn, $courses_query);
            $total_assigned = mysqli_num_rows($courses_result);
            ?>
            <div class="section-box">
                <h2>📚 My Assigned Courses</h2>
                <?php if ($total_assigned == 0): ?>
                    <div class="no-data">No courses assigned yet. Contact administrator.</div>
                <?php else: ?>
                    <table class="data-table">
                        <thead><tr><th>Unit Code</th><th>Unit Name</th><th>Year</th><th>Semester</th><th>Day</th><th>Time</th><th>Venue</th></tr></thead>
                        <tbody>
                            <?php while($course = mysqli_fetch_assoc($courses_result)): ?>
                            <tr>
                                <td><strong><?php echo $course['unit_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($course['unit_name']); ?></td>
                                <td><?php echo $course['year_level']; ?></td>
                                <td><?php echo $course['semester_level']; ?></td>
                                <td><?php echo $course['day_of_week'] ?? 'TBA'; ?></td>
                                <td><?php echo ($course['time_from'] ? $course['time_from'] . ' - ' . $course['time_to'] : 'TBA'); ?></td>
                                <td><?php echo $course['venue'] ?? 'TBA'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php break;

        case 'materials':
            $units_query = "SELECT DISTINCT t.unit_code, aw.unit_name FROM timetable t 
                           JOIN academic_workload aw ON t.unit_code = aw.unit_code 
                           WHERE t.lecturer = '$lecturer_name'";
            $units_result = mysqli_query($conn, $units_query);
            $selected_unit = $_GET['unit'] ?? '';
            
            $materials_query = "SELECT * FROM course_materials WHERE uploaded_by = '$lecturer_name'";
            if ($selected_unit) {
                $materials_query .= " AND unit_code = '$selected_unit'";
            }
            $materials_query .= " ORDER BY uploaded_at DESC";
            $materials_result = mysqli_query($conn, $materials_query);
            ?>
            <div class="section-box">
                <h2>📄 Course Materials Management</h2>
                
                <div class="filter-bar">
                    <div class="filter-group">
                        <label>Filter by Unit:</label>
                        <select onchange="location.href='?section=materials&unit='+this.value">
                            <option value="">All Units</option>
                            <?php while($unit = mysqli_fetch_assoc($units_result)): ?>
                                <option value="<?php echo $unit['unit_code']; ?>" <?php echo ($selected_unit == $unit['unit_code']) ? 'selected' : ''; ?>>
                                    <?php echo $unit['unit_code'] . ' - ' . $unit['unit_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                    <button onclick="openModal('pdfModal')" class="btn btn-primary">📄 Upload PDF/Document</button>
                    <button onclick="openModal('youtubeModal')" class="btn btn-primary">▶️ Add YouTube Tutorial</button>
                </div>
                
                <h3>Uploaded Materials</h3>
                <?php if (mysqli_num_rows($materials_result) == 0): ?>
                    <div class="no-data">No materials uploaded yet.</div>
                <?php else: ?>
                    <?php while($material = mysqli_fetch_assoc($materials_result)): ?>
                        <div class="material-card">
                            <div class="material-title"><?php echo htmlspecialchars($material['title']); ?></div>
                            <div><strong>Unit:</strong> <?php echo $material['unit_code']; ?></div>
                            <div><strong>Description:</strong> <?php echo htmlspecialchars($material['description']); ?></div>
                            <?php if ($material['material_type'] == 'youtube' && $material['youtube_url']): ?>
                                <div class="youtube-preview">
                                    <iframe src="https://www.youtube.com/embed/<?php echo $material['video_id']; ?>" frameborder="0" allowfullscreen></iframe>
                                </div>
                                <div><a href="<?php echo $material['youtube_url']; ?>" target="_blank">🔗 Open on YouTube</a></div>
                            <?php elseif ($material['file_path']): ?>
                                <div><a href="../uploads/materials/<?php echo $material['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">📥 Download Material</a></div>
                            <?php endif; ?>
                            <div style="margin-top: 10px;">
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                    <button type="submit" name="delete_material" class="btn btn-danger btn-sm" onclick="return confirm('Delete this material?')">🗑️ Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        <?php break;

        case 'assignments':
            $units_query = "SELECT DISTINCT t.unit_code, aw.unit_name FROM timetable t 
                           JOIN academic_workload aw ON t.unit_code = aw.unit_code 
                           WHERE t.lecturer = '$lecturer_name'";
            $units_result = mysqli_query($conn, $units_query);
            $selected_unit = $_GET['unit'] ?? '';
            
            $assignments_query = "SELECT * FROM assignments WHERE created_by = '$lecturer_name'";
            if ($selected_unit) {
                $assignments_query .= " AND unit_code = '$selected_unit'";
            }
            $assignments_query .= " ORDER BY created_at DESC";
            $assignments_result = mysqli_query($conn, $assignments_query);
            ?>
            <div class="section-box">
                <h2>📝 Assessments Management (CATs, Assignments, Exams)</h2>
                
                <div class="filter-bar">
                    <div class="filter-group">
                        <label>Filter by Unit:</label>
                        <select onchange="location.href='?section=assignments&unit='+this.value">
                            <option value="">All Units</option>
                            <?php while($unit = mysqli_fetch_assoc($units_result)): ?>
                                <option value="<?php echo $unit['unit_code']; ?>" <?php echo ($selected_unit == $unit['unit_code']) ? 'selected' : ''; ?>>
                                    <?php echo $unit['unit_code'] . ' - ' . $unit['unit_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <button onclick="openModal('assignmentModal')" class="btn btn-primary" style="margin-bottom: 20px;">📝 Upload New Assessment</button>
                
                <h3>Posted Assessments</h3>
                <?php if (mysqli_num_rows($assignments_result) == 0): ?>
                    <div class="no-data">No assessments uploaded yet.</div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr><th>Title</th><th>Unit</th><th>Type</th><th>Due Date</th><th>Max Marks</th><th>Created</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php while($assignment = mysqli_fetch_assoc($assignments_result)): 
                                $type_class = ($assignment['assessment_type'] == 'CAT') ? 'type-cat' : (($assignment['assessment_type'] == 'Exam') ? 'type-exam' : 'type-assignment');
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($assignment['title']); ?></strong><br><small><?php echo htmlspecialchars($assignment['description']); ?></small></td>
                                <td><?php echo $assignment['unit_code']; ?></td>
                                <td><span class="type-badge <?php echo $type_class; ?>"><?php echo $assignment['assessment_type']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($assignment['due_date'])); ?></td>
                                <td><?php echo $assignment['total_marks']; ?></td>
                                <td><?php echo date('d M Y', strtotime($assignment['created_at'])); ?></td>
                                <td>
                                    <a href="../uploads/assignments/<?php echo $assignment['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">📥 Download</a>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                        <button type="submit" name="delete_assignment" class="btn btn-danger btn-sm" onclick="return confirm('Delete this assessment?')">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php break;

        case 'submissions':
            $units_query = "SELECT DISTINCT t.unit_code, aw.unit_name 
                           FROM timetable t 
                           JOIN academic_workload aw ON t.unit_code = aw.unit_code 
                           WHERE t.lecturer = '$lecturer_name'";
            $units_result = mysqli_query($conn, $units_query);
            $selected_unit = $_GET['unit'] ?? '';
            
            $students_query = "SELECT id, full_name, reg_number, email FROM users WHERE role='student' AND department='$lecturer_dept' ORDER BY full_name";
            $students_result = mysqli_query($conn, $students_query);
            
            $submissions_query = "SELECT s.*, a.title as assignment_title, a.total_marks, a.assessment_type, a.unit_code as assignment_unit_code, u.full_name, u.reg_number, u.email 
                                 FROM assignment_submissions s 
                                 JOIN assignments a ON s.assignment_id = a.id 
                                 JOIN users u ON s.student_reg = u.reg_number 
                                 WHERE a.created_by = '$lecturer_name' AND s.assignment_id != 0";
            
            if ($selected_unit) {
                $submissions_query .= " AND a.unit_code = '$selected_unit'";
            }
            if ($submission_filter_status == 'graded') {
                $submissions_query .= " AND s.status = 'graded'";
            } elseif ($submission_filter_status == 'pending') {
                $submissions_query .= " AND s.status = 'submitted'";
            }
            if ($submission_filter_type == 'CAT') {
                $submissions_query .= " AND a.assessment_type = 'CAT'";
            } elseif ($submission_filter_type == 'Assignment') {
                $submissions_query .= " AND a.assessment_type = 'Assignment'";
            } elseif ($submission_filter_type == 'Exam') {
                $submissions_query .= " AND a.assessment_type = 'Exam'";
            }
            
            $submissions_query .= " ORDER BY s.submitted_at DESC";
            $submissions_result = mysqli_query($conn, $submissions_query);
            ?>
            <div class="section-box">
                <h2>📋 Student Submissions</h2>
                
                <div class="filter-bar">
                    <div class="filter-group">
                        <label>Filter by Unit:</label>
                        <select onchange="location.href='?section=submissions&unit='+this.value+'&submission_status=<?php echo $submission_filter_status; ?>&submission_type=<?php echo $submission_filter_type; ?>'">
                            <option value="">All Units</option>
                            <?php 
                            mysqli_data_seek($units_result, 0);
                            while($unit = mysqli_fetch_assoc($units_result)): ?>
                                <option value="<?php echo $unit['unit_code']; ?>" <?php echo ($selected_unit == $unit['unit_code']) ? 'selected' : ''; ?>>
                                    <?php echo $unit['unit_code'] . ' - ' . $unit['unit_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Filter by Status:</label>
                        <select onchange="location.href='?section=submissions&unit=<?php echo $selected_unit; ?>&submission_status='+this.value+'&submission_type=<?php echo $submission_filter_type; ?>'">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $submission_filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="graded" <?php echo $submission_filter_status == 'graded' ? 'selected' : ''; ?>>Graded</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Filter by Type:</label>
                        <select onchange="location.href='?section=submissions&unit=<?php echo $selected_unit; ?>&submission_status=<?php echo $submission_filter_status; ?>&submission_type='+this.value">
                            <option value="">All Types</option>
                            <option value="CAT" <?php echo $submission_filter_type == 'CAT' ? 'selected' : ''; ?>>📝 CAT</option>
                            <option value="Assignment" <?php echo $submission_filter_type == 'Assignment' ? 'selected' : ''; ?>>📚 Assignment</option>
                            <option value="Exam" <?php echo $submission_filter_type == 'Exam' ? 'selected' : ''; ?>>📖 Exam</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <a href="?section=submissions">Clear Filters</a>
                    </div>
                    <div class="filter-group">
                        <button onclick="openModal('manualMarksModal')" class="btn btn-warning">📝 + Add Manual Marks</button>
                    </div>
                </div>
                
                <!-- Manual Marks Modal -->
                <div id="manualMarksModal" class="modal">
                    <div class="modal-content" style="max-width: 500px;">
                        <h3>📝 Add Manual Marks (Physical Submissions)</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="save_manual_marks" value="1">
                            <div class="form-group">
                                <label>Select Student *</label>
                                <select name="student_reg" required>
                                    <option value="">-- Select Student --</option>
                                    <?php 
                                    mysqli_data_seek($students_result, 0);
                                    while($student = mysqli_fetch_assoc($students_result)): ?>
                                        <option value="<?php echo $student['reg_number']; ?>">
                                            <?php echo $student['full_name'] . ' (' . $student['reg_number'] . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Select Unit *</label>
                                <select name="unit_code" required>
                                    <option value="">-- Select Unit --</option>
                                    <?php 
                                    mysqli_data_seek($units_result, 0);
                                    while($unit = mysqli_fetch_assoc($units_result)): ?>
                                        <option value="<?php echo $unit['unit_code']; ?>">
                                            <?php echo $unit['unit_code'] . ' - ' . $unit['unit_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Assessment Type *</label>
                                <select name="assessment_type" required id="manual_assessment_type" onchange="updateManualMaxMarks()">
                                    <option value="">-- Select Type --</option>
                                    <option value="CAT">📝 CAT (Max 20 marks)</option>
                                    <option value="Assignment">📚 Assignment (Max 10 marks)</option>
                                    <option value="Exam">📖 Main Exam (Max 70 marks)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Assessment Name *</label>
                                <input type="text" name="assessment_name" required placeholder="e.g., CAT 1, Assignment 2, Final Exam">
                            </div>
                            
                            <div class="form-group">
                                <label>Total Marks</label>
                                <input type="number" id="manual_total_marks" readonly style="background: #f3f4f6;" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label>Obtained Marks *</label>
                                <input type="number" name="obtained_marks" id="manual_obtained_marks" required min="0" step="0.5">
                            </div>
                            
                            <div class="form-group">
                                <label>Feedback (Optional)</label>
                                <textarea name="feedback" rows="3" placeholder="Add comments or feedback..."></textarea>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button type="submit" class="btn btn-success">Save Marks</button>
                                <button type="button" onclick="closeModal('manualMarksModal')" class="btn btn-danger">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (mysqli_num_rows($submissions_result) == 0): ?>
                    <div class="no-data">
                        <p>📭 No student submissions found with the selected filters.</p>
                        <p>Click "Add Manual Marks" to record marks for physical submissions.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Reg Number</th>
                                    <th>Assessment</th>
                                    <th>Unit</th>
                                    <th>Type</th>
                                    <th>Submitted On</th>
                                    <th>Marks</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sub = mysqli_fetch_assoc($submissions_result)): 
                                    $type_class = ($sub['assessment_type'] == 'CAT') ? 'type-cat' : (($sub['assessment_type'] == 'Exam') ? 'type-exam' : 'type-assignment');
                                    $type_icon = ($sub['assessment_type'] == 'CAT') ? '📝' : (($sub['assessment_type'] == 'Exam') ? '📖' : '📚');
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($sub['full_name']); ?></strong><br>
                                            <small><?php echo $sub['email']; ?></small>
                                         </div>
                                        <td><?php echo $sub['reg_number']; ?></div>
                                        <td><?php echo htmlspecialchars($sub['assignment_title']); ?></div>
                                        <td><?php echo $sub['assignment_unit_code']; ?></div>
                                        <td><span class="type-badge <?php echo $type_class; ?>"><?php echo $type_icon . ' ' . $sub['assessment_type']; ?></span></div>
                                        <td><?php echo date('d M Y H:i', strtotime($sub['submitted_at'])); ?></div>
                                        <td>
                                            <?php if($sub['obtained_marks'] !== null): ?>
                                                <span class="marks-awarded"><?php echo $sub['obtained_marks']; ?> / <?php echo $sub['total_marks']; ?></span>
                                            <?php else: ?>
                                                <span class="marks-pending">Not graded</span>
                                            <?php endif; ?>
                                         </div>
                                        <td>
                                            <?php if($sub['status'] == 'graded'): ?>
                                                <span class="status-badge" style="background: #d1fae5; color: #065f46;">✅ Graded</span>
                                            <?php else: ?>
                                                <span class="status-badge" style="background: #fed7aa; color: #92400e;">⏳ Pending</span>
                                            <?php endif; ?>
                                         </div>
                                        <td>
                                            <button onclick="openGradeModal(<?php echo $sub['id']; ?>, '<?php echo addslashes($sub['full_name']); ?>', '<?php echo addslashes($sub['assignment_title']); ?>', <?php echo $sub['total_marks']; ?>, <?php echo $sub['obtained_marks'] ?? 'null'; ?>, '<?php echo addslashes($sub['feedback'] ?? ''); ?>', '<?php echo $sub['assessment_type']; ?>')" class="btn btn-primary btn-sm">
                                                <?php echo ($sub['obtained_marks'] !== null) ? '✏️ Edit Marks' : '🎯 Award Marks'; ?>
                                            </button>
                                            <?php if($sub['file_path']): ?>
                                                <a href="../uploads/submissions/<?php echo $sub['file_path']; ?>" target="_blank" class="btn btn-info btn-sm">📄 View File</a>
                                            <?php endif; ?>
                                            <?php if($sub['submission_text'] && !empty(trim($sub['submission_text']))): ?>
                                                <button onclick="viewTextSubmission('<?php echo addslashes($sub['submission_text']); ?>')" class="btn btn-info btn-sm">📝 View Answer</button>
                                            <?php endif; ?>
                                         </div>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php break;

        case 'students':
            $students_query = "SELECT id, full_name, reg_number, email, phone, created_at FROM users WHERE role='student' AND department='$lecturer_dept' ORDER BY full_name";
            $students_result = mysqli_query($conn, $students_query);
            ?>
            <div class="section-box">
                <h2>👨‍🎓 Students in <?php echo htmlspecialchars($lecturer_dept); ?> Department</h2>
                <table class="data-table">
                    <thead><tr><th>Full Name</th><th>Registration Number</th><th>Email</th><th>Phone</th></tr></thead>
                    <tbody>
                        <?php while($student = mysqli_fetch_assoc($students_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo $student['reg_number']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['phone'] ?? 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php break;

        case 'timetable':
            $timetable_query = "SELECT t.*, aw.unit_name FROM timetable t JOIN academic_workload aw ON t.unit_code = aw.unit_code WHERE t.lecturer = '$lecturer_name' ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.time_from";
            $timetable_result = mysqli_query($conn, $timetable_query);
            ?>
            <div class="section-box">
                <h2>📅 My Teaching Timetable</h2>
                <?php if (mysqli_num_rows($timetable_result) == 0): ?>
                    <div class="no-data">No scheduled classes yet.</div>
                <?php else: ?>
                    <table class="data-table">
                        <thead><tr><th>Unit Code</th><th>Course Title</th><th>Day</th><th>Time</th><th>Venue</th></td></thead>
                        <tbody>
                            <?php while($tt = mysqli_fetch_assoc($timetable_result)): ?>
                            <tr>
                                <td><strong><?php echo $tt['unit_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($tt['unit_name']); ?></td>
                                <td><?php echo $tt['day_of_week']; ?></td>
                                <td><?php echo $tt['time_from'] . ' - ' . $tt['time_to']; ?></td>
                                <td><?php echo $tt['venue']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php break;
    }
    ?>
</div>

<!-- PDF Upload Modal -->
<div id="pdfModal" class="modal">
    <div class="modal-content">
        <h3>📄 Upload Learning Material</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="upload_material" value="1">
            <div class="form-group">
                <label>Select Unit *</label>
                <select name="unit_code" required>
                    <option value="">-- Select Unit --</option>
                    <?php 
                    $units = mysqli_query($conn, "SELECT DISTINCT t.unit_code, aw.unit_name FROM timetable t JOIN academic_workload aw ON t.unit_code = aw.unit_code WHERE t.lecturer = '$lecturer_name'");
                    while($u = mysqli_fetch_assoc($units)): ?>
                        <option value="<?php echo $u['unit_code']; ?>"><?php echo $u['unit_code'] . ' - ' . $u['unit_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="e.g., Introduction to Programming">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Brief description of the material"></textarea>
            </div>
            <div class="form-group">
                <label>File (PDF, DOC, PPT, JPG, PNG) *</label>
                <input type="file" name="material_file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.png">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success">Upload</button>
                <button type="button" onclick="closeModal('pdfModal')" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- YouTube Modal -->
<div id="youtubeModal" class="modal">
    <div class="modal-content">
        <h3>▶️ Add YouTube Tutorial</h3>
        <form method="POST">
            <input type="hidden" name="upload_youtube" value="1">
            <div class="form-group">
                <label>Select Unit *</label>
                <select name="unit_code" required>
                    <option value="">-- Select Unit --</option>
                    <?php 
                    mysqli_data_seek($units, 0);
                    while($u = mysqli_fetch_assoc($units)): ?>
                        <option value="<?php echo $u['unit_code']; ?>"><?php echo $u['unit_code'] . ' - ' . $u['unit_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="e.g., Complete Python Tutorial">
            </div>
            <div class="form-group">
                <label>YouTube URL *</label>
                <input type="url" name="youtube_url" required placeholder="https://www.youtube.com/watch?v=...">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="What will students learn?"></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success">Add YouTube Link</button>
                <button type="button" onclick="closeModal('youtubeModal')" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Assignment Modal with Assessment Type -->
<div id="assignmentModal" class="modal">
    <div class="modal-content">
        <h3>📝 Upload Assessment (CAT/Assignment/Exam)</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="upload_assignment" value="1">
            <div class="form-group">
                <label>Select Unit *</label>
                <select name="unit_code" required>
                    <option value="">-- Select Unit --</option>
                    <?php 
                    mysqli_data_seek($units, 0);
                    while($u = mysqli_fetch_assoc($units)): ?>
                        <option value="<?php echo $u['unit_code']; ?>"><?php echo $u['unit_code'] . ' - ' . $u['unit_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Assessment Type *</label>
                <select name="assessment_type" required id="assessment_type" onchange="updateMaxMarks()">
                    <option value="">-- Select Type --</option>
                    <option value="CAT">📝 CAT (Continuous Assessment Test) - Max 20 marks</option>
                    <option value="Assignment">📚 Assignment - Max 10 marks</option>
                    <option value="Exam">📖 Main Exam - Max 70 marks</option>
                </select>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="e.g., CAT 1 - Introduction to Programming">
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" required placeholder="Instructions and requirements"></textarea>
            </div>
            <div class="form-group">
                <label>Due Date *</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label>Total Marks</label>
                <input type="number" name="total_marks" id="total_marks" readonly style="background: #f3f4f6;" value="0">
                <small id="marks_info">Select assessment type to see max marks</small>
            </div>
            <div class="form-group">
                <label>Assessment File (PDF, DOC, DOCX, ZIP) *</label>
                <input type="file" name="assignment_file" required accept=".pdf,.doc,.docx,.zip">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success">Upload</button>
                <button type="button" onclick="closeModal('assignmentModal')" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Grade Modal -->
<div id="gradeModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3>🎯 Award Marks</h3>
        <form method="POST" action="">
            <input type="hidden" name="submission_id" id="grade_submission_id">
            <input type="hidden" name="grade_submission" value="1">
            
            <div class="form-group">
                <label>Student Name</label>
                <input type="text" id="grade_student_name" readonly style="background: #f3f4f6;">
            </div>
            
            <div class="form-group">
                <label>Assessment</label>
                <input type="text" id="grade_assignment_title" readonly style="background: #f3f4f6;">
            </div>
            
            <div class="form-group">
                <label>Assessment Type</label>
                <input type="text" id="grade_assessment_type" readonly style="background: #f3f4f6;">
            </div>
            
            <div class="form-group">
                <label>Total Marks</label>
                <input type="text" id="grade_total_marks" readonly style="background: #f3f4f6;">
            </div>
            
            <div class="form-group">
                <label>Obtained Marks *</label>
                <input type="number" name="obtained_marks" id="grade_obtained_marks" required min="0" step="0.5" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                <small id="marks_warning" style="color: #f59e0b;"></small>
            </div>
            
            <div class="form-group">
                <label>Feedback (Optional)</label>
                <textarea name="feedback" id="grade_feedback" rows="4" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;" placeholder="Provide feedback to the student..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-success">Save Marks</button>
                <button type="button" onclick="closeGradeModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Text Submission Modal -->
<div id="textSubmissionModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <h3>📝 Student's Answer</h3>
        <div id="text_submission_content" style="background: #f8fafc; padding: 20px; border-radius: 8px; margin: 15px 0; max-height: 400px; overflow-y: auto; white-space: pre-wrap; font-family: inherit;"></div>
        <div style="display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeTextSubmissionModal()" class="btn btn-danger">Close</button>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <h3>🔐 Change Your Password</h3>
        <form method="POST">
            <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required></div>
            <div class="form-group"><label>New Password</label><input type="password" name="new_password" required minlength="6"></div>
            <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" required></div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="change_password" class="btn btn-success">Change Password</button>
                <button type="button" onclick="closePasswordModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) { 
    document.getElementById(modalId).style.display = 'flex'; 
}
function closeModal(modalId) { 
    document.getElementById(modalId).style.display = 'none'; 
}
function openPasswordModal() { 
    document.getElementById('passwordModal').style.display = 'flex'; 
}
function closePasswordModal() { 
    document.getElementById('passwordModal').style.display = 'none'; 
}

function updateMaxMarks() {
    const type = document.getElementById('assessment_type').value;
    const marksField = document.getElementById('total_marks');
    const infoSpan = document.getElementById('marks_info');
    
    if (type === 'CAT') {
        marksField.value = 20;
        infoSpan.innerHTML = '📝 CATs are marked out of 20 marks';
        marksField.style.background = '#d1fae5';
    } else if (type === 'Assignment') {
        marksField.value = 10;
        infoSpan.innerHTML = '📚 Assignments are marked out of 10 marks';
        marksField.style.background = '#d1fae5';
    } else if (type === 'Exam') {
        marksField.value = 70;
        infoSpan.innerHTML = '📖 Main Exams are marked out of 70 marks';
        marksField.style.background = '#d1fae5';
    } else {
        marksField.value = '';
        infoSpan.innerHTML = 'Select assessment type to see max marks';
        marksField.style.background = '#f3f4f6';
    }
}

function openGradeModal(id, studentName, assignmentTitle, totalMarks, obtainedMarks, feedback, assessmentType) {
    document.getElementById('grade_submission_id').value = id;
    document.getElementById('grade_student_name').value = studentName;
    document.getElementById('grade_assignment_title').value = assignmentTitle;
    document.getElementById('grade_assessment_type').value = assessmentType;
    document.getElementById('grade_total_marks').value = totalMarks;
    document.getElementById('grade_obtained_marks').value = obtainedMarks !== null ? obtainedMarks : '';
    document.getElementById('grade_feedback').value = feedback !== 'null' ? feedback : '';
    
    const marksInput = document.getElementById('grade_obtained_marks');
    const warningSpan = document.getElementById('marks_warning');
    
    marksInput.oninput = function() {
        const value = parseFloat(this.value);
        if (value > totalMarks) {
            warningSpan.innerHTML = '⚠️ Warning: Marks exceed maximum (' + totalMarks + ' marks)!';
            this.style.borderColor = '#ef4444';
        } else {
            warningSpan.innerHTML = '';
            this.style.borderColor = '#e2e8f0';
        }
    };
    
    marksInput.max = totalMarks;
    
    document.getElementById('gradeModal').style.display = 'flex';
}

function closeGradeModal() {
    document.getElementById('gradeModal').style.display = 'none';
}

function viewTextSubmission(text) {
    document.getElementById('text_submission_content').innerHTML = text.replace(/\n/g, '<br>');
    document.getElementById('textSubmissionModal').style.display = 'flex';
}

function closeTextSubmissionModal() {
    document.getElementById('textSubmissionModal').style.display = 'none';
}

function updateManualMaxMarks() {
    const type = document.getElementById('manual_assessment_type').value;
    const marksField = document.getElementById('manual_total_marks');
    const obtainedField = document.getElementById('manual_obtained_marks');
    
    if (type === 'CAT') {
        marksField.value = 20;
        marksField.style.background = '#d1fae5';
        if (obtainedField.value > 20) obtainedField.value = 20;
        obtainedField.max = 20;
    } else if (type === 'Assignment') {
        marksField.value = 10;
        marksField.style.background = '#d1fae5';
        if (obtainedField.value > 10) obtainedField.value = 10;
        obtainedField.max = 10;
    } else if (type === 'Exam') {
        marksField.value = 70;
        marksField.style.background = '#d1fae5';
        if (obtainedField.value > 70) obtainedField.value = 70;
        obtainedField.max = 70;
    } else {
        marksField.value = '';
        marksField.style.background = '#f3f4f6';
        obtainedField.max = '';
    }
}

window.onclick = function(event) {
    const modals = ['pdfModal', 'youtubeModal', 'assignmentModal', 'passwordModal', 'gradeModal', 'textSubmissionModal', 'manualMarksModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (event.target == modal) modal.style.display = 'none';
    });
}
</script>
</body>
</html>