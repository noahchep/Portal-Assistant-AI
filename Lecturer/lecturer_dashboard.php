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
        
        // Extract YouTube video ID
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
        $total_marks = intval($_POST['total_marks']);
        
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
                $insert = "INSERT INTO assignments (unit_code, title, description, file_path, due_date, total_marks, created_by, created_at) 
                          VALUES ('$unit_code', '$title', '$description', '$file_name', '$due_date', '$total_marks', '$lecturer_name', NOW())";
                if (mysqli_query($conn, $insert)) {
                    echo '<div class="alert-success">✅ Assignment uploaded successfully!</div>';
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
        .btn-sm { padding: 4px 10px; font-size: 0.75rem; }

        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; }

        .password-banner { background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); border-left: 4px solid #f59e0b; padding: 20px; margin-bottom: 30px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .change-password-btn { background: #f59e0b; color: white; padding: 10px 24px; border-radius: 8px; border: none; cursor: pointer; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; }
        .form-group textarea { min-height: 80px; }

        .material-card { background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .material-title { font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .youtube-preview { margin-top: 10px; }
        .youtube-preview iframe { width: 100%; max-width: 300px; height: 170px; border-radius: 8px; }

        .no-data { text-align: center; padding: 40px; color: #666; background: #f9fafb; border-radius: 8px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border); }
        .tab-btn { background: none; border: none; padding: 10px 20px; cursor: pointer; font-weight: 600; color: var(--text-light); }
        .tab-btn.active { color: var(--primary); border-bottom: 2px solid var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
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
                    <li>✅ Create and upload assignments</li>
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
            // Get assigned units for dropdown
            $units_query = "SELECT DISTINCT t.unit_code, aw.unit_name FROM timetable t 
                           JOIN academic_workload aw ON t.unit_code = aw.unit_code 
                           WHERE t.lecturer = '$lecturer_name'";
            $units_result = mysqli_query($conn, $units_query);
            $selected_unit = $_GET['unit'] ?? '';
            
            // Get materials for selected unit
            $materials_query = "SELECT * FROM course_materials WHERE uploaded_by = '$lecturer_name'";
            if ($selected_unit) {
                $materials_query .= " AND unit_code = '$selected_unit'";
            }
            $materials_query .= " ORDER BY uploaded_at DESC";
            $materials_result = mysqli_query($conn, $materials_query);
            ?>
            <div class="section-box">
                <h2>📄 Course Materials Management</h2>
                
                <!-- Unit Filter -->
                <div style="margin-bottom: 20px;">
                    <label>Filter by Unit:</label>
                    <select onchange="location.href='?section=materials&unit='+this.value" style="padding: 8px; margin-left: 10px;">
                        <option value="">All Units</option>
                        <?php while($unit = mysqli_fetch_assoc($units_result)): ?>
                            <option value="<?php echo $unit['unit_code']; ?>" <?php echo ($selected_unit == $unit['unit_code']) ? 'selected' : ''; ?>>
                                <?php echo $unit['unit_code'] . ' - ' . $unit['unit_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Upload Buttons -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                    <button onclick="openModal('pdfModal')" class="btn btn-primary">📄 Upload PDF/Document</button>
                    <button onclick="openModal('youtubeModal')" class="btn btn-primary">▶️ Add YouTube Tutorial</button>
                </div>
                
                <!-- Materials List -->
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
                <h2>📝 Assignments Management</h2>
                
                <div style="margin-bottom: 20px;">
                    <label>Filter by Unit:</label>
                    <select onchange="location.href='?section=assignments&unit='+this.value" style="padding: 8px; margin-left: 10px;">
                        <option value="">All Units</option>
                        <?php while($unit = mysqli_fetch_assoc($units_result)): ?>
                            <option value="<?php echo $unit['unit_code']; ?>" <?php echo ($selected_unit == $unit['unit_code']) ? 'selected' : ''; ?>>
                                <?php echo $unit['unit_code'] . ' - ' . $unit['unit_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button onclick="openModal('assignmentModal')" class="btn btn-primary" style="margin-bottom: 20px;">📝 Upload New Assignment</button>
                
                <h3>Posted Assignments</h3>
                <?php if (mysqli_num_rows($assignments_result) == 0): ?>
                    <div class="no-data">No assignments uploaded yet.</div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr><th>Title</th><th>Unit</th><th>Due Date</th><th>Total Marks</th><th>Created</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php while($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($assignment['title']); ?></strong><br><small><?php echo htmlspecialchars($assignment['description']); ?></small></td>
                                <td><?php echo $assignment['unit_code']; ?></td>
                                <td><?php echo date('d M Y', strtotime($assignment['due_date'])); ?></td>
                                <td><?php echo $assignment['total_marks']; ?></td>
                                <td><?php echo date('d M Y', strtotime($assignment['created_at'])); ?></td>
                                <td>
                                    <a href="../uploads/assignments/<?php echo $assignment['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">📥 Download</a>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                        <button type="submit" name="delete_assignment" class="btn btn-danger btn-sm" onclick="return confirm('Delete this assignment?')">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
                        <thead><tr><th>Unit Code</th><th>Course Title</th><th>Day</th><th>Time</th><th>Venue</th></tr></thead>
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

<!-- Assignment Modal -->
<div id="assignmentModal" class="modal">
    <div class="modal-content">
        <h3>📝 Upload Assignment</h3>
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
                <label>Assignment Title *</label>
                <input type="text" name="title" required placeholder="e.g., Week 3 Assignment">
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" required placeholder="Assignment instructions and requirements"></textarea>
            </div>
            <div class="form-group">
                <label>Due Date *</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label>Total Marks *</label>
                <input type="number" name="total_marks" required placeholder="e.g., 100">
            </div>
            <div class="form-group">
                <label>Assignment File (PDF, DOC, DOCX, ZIP) *</label>
                <input type="file" name="assignment_file" required accept=".pdf,.doc,.docx,.zip">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success">Upload Assignment</button>
                <button type="button" onclick="closeModal('assignmentModal')" class="btn btn-danger">Cancel</button>
            </div>
        </form>
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
function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
function openPasswordModal() { document.getElementById('passwordModal').style.display = 'flex'; }
function closePasswordModal() { document.getElementById('passwordModal').style.display = 'none'; }
window.onclick = function(event) {
    const modals = ['pdfModal', 'youtubeModal', 'assignmentModal', 'passwordModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (event.target == modal) modal.style.display = 'none';
    });
}
</script>
</body>
</html>