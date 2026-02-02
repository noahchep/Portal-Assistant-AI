<?php
session_start();

/* ==========================
   ACCESS CONTROL
========================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Administrator';

/* ==========================
   DATABASE CONNECTION
========================== */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* ==========================
   DASHBOARD METRICS
========================== */
$total_students = 0;
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='student'");
if ($q1) { $total_students = mysqli_fetch_assoc($q1)['total']; }

$total_units = 0;
$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM timetable");
if ($q2) { $total_units = mysqli_fetch_assoc($q2)['total']; }

// Determine current section
$section = $_GET['section'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Student Support Agent</title>
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
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        /* HEADER */
        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; }
        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 50px; border-radius: 8px; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; }

        /* NAVIGATION */
        nav { background: var(--primary); padding: 0 5%; }
        .nav-top { display: flex; gap: 10px; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; border-bottom: 3px solid transparent; }
        nav a:hover { color: white; background: rgba(255,255,255,0.1); }
        nav a.active { color: white; background: rgba(255,255,255,0.15); border-bottom: 3px solid white; }

        .nav-sub { background: #f1f5f9; display: flex; gap: 10px; padding: 0 5%; border-bottom: 1px solid var(--border); }
        .nav-sub a { color: var(--text-light); font-size: 0.8rem; padding: 10px 15px; text-decoration: none; font-weight: 600; }
        .nav-sub a:hover { color: var(--primary); }
        .nav-sub .active { color: var(--primary); font-weight: 700; border-bottom: 2px solid var(--primary); }

        /* CONTAINER */
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        
        .admin-strip { background: var(--accent); padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; color: var(--primary-dark); font-weight: 700; font-size: 0.85rem; }

        /* DASHBOARD CARDS */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: 0.3s; }
        .stat-card.actionable:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-color: var(--primary); }
        .stat-card h3 { margin: 0; font-size: 2rem; color: var(--primary); font-weight: 800; }
        .stat-card p { margin: 5px 0 0 0; color: var(--text-light); font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; }

        /* TABLES */
        .section-box { background: var(--white); border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8fafc; padding: 12px; font-size: 0.75rem; color: var(--text-light); border-bottom: 2px solid var(--border); text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid var(--border); font-size: 0.85rem; }

        .ai-note { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; font-size: 0.85rem; color: #92400e; }
        footer { text-align: center; padding: 40px; color: var(--text-light); font-size: 0.85rem; }
    </style>
</head>
<body>

<header>
    <div class="branding">
        <img src="../Images/logo.jpg" class="logoimg" alt="Logo">
        <div>
            <h1>Student Support Agent – Admin</h1>
            <small>Academic Administration Portal</small>
        </div>
    </div>
</header>

<nav>
    <div class="nav-top">
        <a href="Admin-index.php" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
        <a href="view_students.php?section=students" class="<?php echo $section === 'students' ? 'active' : ''; ?>">Manage Students</a>
        <a href="Admin-index.php?section=units" class="<?php echo $section === 'units' ? 'active' : ''; ?>">Manage Units</a>
        <a href="Admin-index.php?section=registrations" class="<?php echo $section === 'registrations' ? 'active' : ''; ?>">Registrations</a>
        <a href="../logout.php">Sign Out</a>
    </div>
</nav>

<?php if ($section !== 'dashboard'): ?>
<div class="nav-sub">
    <?php if ($section === 'units'): ?>
        <a href="add_unit.php">Add Unit</a>
        <a href="add_workload.php">Add Workload</a>
        <a href="#" class="active">View Workload</a>
    <?php elseif ($section === 'students'): ?>
        <a href="add_student.php">Add Student</a>
        <a href="#" class="active">Student Directory</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="container">
    <div class="admin-strip">
        <span>Active Admin: <strong><?php echo htmlspecialchars($admin_name); ?></strong></span>
        <span>Department of Registrar (Academic)</span>
    </div>

    <?php if ($section === 'dashboard'): ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students Registered</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_units; ?></h3>
                <p>Units on Offer</p>
            </div>
            
            <a href="Admin-index.php?section=units" style="text-decoration: none;">
                <div class="stat-card actionable" style="border-top: 4px solid var(--success);">
                    <h3 style="color: var(--success);">View →</h3>
                    <p>Academic Year Workload</p>
                </div>
            </a>
        </div>

        <h2 style="font-size: 1.1rem; margin-bottom: 20px;">Administrative Quick Links</h2>
        <div class="dashboard-grid">
            <a href="Admin-index.php?section=students" style="text-decoration: none;">
                <div class="stat-card actionable">
                    <div style="font-size: 1.5rem; margin-bottom: 10px;">👥</div>
                    <p style="color: var(--primary);">Student Records</p>
                </div>
            </a>
            <a href="Admin-index.php?section=units" style="text-decoration: none;">
                <div class="stat-card actionable">
                    <div style="font-size: 1.5rem; margin-bottom: 10px;">📅</div>
                    <p style="color: var(--primary);">Timetable Management</p>
                </div>
            </a>
            <a href="Admin-index.php?section=registrations" style="text-decoration: none;">
                <div class="stat-card actionable">
                    <div style="font-size: 1.5rem; margin-bottom: 10px;">✍️</div>
                    <p style="color: var(--primary);">Registration Logs</p>
                </div>
            </a>
        </div>

        <div class="section-box">
            <div class="ai-note">
                <strong>AI System Health:</strong> The Agent is currently autonomously handling student requests regarding venue identification and unit lookups. Read-only access is enforced on the student-facing chat.
            </div>
        </div>

    <?php elseif ($section === 'units'): ?>
        <div class="section-box">
            <h2 style="margin-top: 0; font-size: 1.2rem;">Current Semester Workload</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Course Title</th>
                            <th>Schedule</th>
                            <th>Venue</th>
                            <th>Lecturer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $units = mysqli_query($conn, "SELECT * FROM timetable ORDER BY id DESC");
                        $i = 1;
                        while ($units && $u = mysqli_fetch_assoc($units)): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><span style="background:var(--accent); color:var(--primary-dark); padding:4px 8px; border-radius:4px; font-weight:700;"><?php echo $u['unit_code']; ?></span></td>
                                <td><?php echo $u['course_title']; ?></td>
                                <td><?php echo $u['time_from']; ?> - <?php echo $u['time_to']; ?></td>
                                <td><?php echo $u['venue']; ?></td>
                                <td><?php echo $u['lecturer']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer>
    &copy; 2026 Portal Assistant AI | Mount Kenya University
</footer>

</body>
</html>