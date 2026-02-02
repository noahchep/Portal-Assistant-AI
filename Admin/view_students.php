<?php
session_start();

/* SECURITY CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'System Admin';

/* DB CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }

/* SEARCH & FILTER LOGIC */
$search = $_GET['search'] ?? '';
$dept_filter = $_GET['dept'] ?? 'all';

// Base Query - Ensure 'role' and 'users' match your actual DB table
$query = "SELECT * FROM users WHERE role='student'";

if ($dept_filter !== 'all') {
    $query .= " AND department = '" . mysqli_real_escape_string($conn, $dept_filter) . "'";
}

if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $query .= " AND (full_name LIKE '%$s%' OR reg_number LIKE '%$s%')";
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// DYNAMIC COUNT - If query fails, this will show 0
if ($result) {
    $current_view_count = mysqli_num_rows($result);
} else {
    $current_view_count = 0;
    // Uncomment the line below to see if there is a SQL error
    // die("Query Error: " . mysqli_error($conn)); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Directory | Admin Portal</title>
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
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        header { 
            background: var(--white); 
            border-bottom: 1px solid var(--border); 
            padding: 0.8rem 5%; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }

        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 55px; width: auto; border-radius: 8px; }
        .branding-text { display: flex; flex-direction: column; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; line-height: 1.2; }
        .branding small { color: var(--text-light); font-size: 0.85rem; font-weight: 500; }

        nav { background: var(--primary); padding: 0 5%; }
        .nav-top { display: flex; gap: 10px; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; border-bottom: 3px solid transparent; transition: 0.3s; }
        nav a:hover { color: white; background: rgba(255,255,255,0.1); }
        nav a.active { color: white; background: rgba(255,255,255,0.15); border-bottom: 3px solid white; }

        .nav-sub { background: #f1f5f9; display: flex; gap: 10px; padding: 0 5%; border-bottom: 1px solid var(--border); }
        .nav-sub a { color: var(--text-light); font-size: 0.8rem; padding: 10px 15px; text-decoration: none; font-weight: 600; }
        .nav-sub a.active { color: var(--primary); font-weight: 700; border-bottom: 2px solid var(--primary); }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        
        .admin-strip { 
            background: var(--accent); 
            padding: 12px 20px; 
            border-radius: 10px; 
            margin-bottom: 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            font-weight: 700; 
            font-size: 0.85rem; 
            color: var(--primary-dark); 
        }

        .search-container { 
            background: var(--white); 
            padding: 20px; 
            border-radius: 12px; 
            border: 1px solid var(--border); 
            margin-bottom: 25px; 
            display: flex; 
            gap: 15px; 
            align-items: center; 
        }
        
        input, select { padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; }
        .search-input { flex-grow: 1; }

        .section-box { background: var(--white); border-radius: 12px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8fafc; padding: 15px; font-size: 0.75rem; color: var(--text-light); border-bottom: 2px solid var(--border); text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
        tr:hover { background-color: #fcfcfc; }
        
        .btn-filter { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .dept-badge { background: var(--accent); color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; }

        footer { text-align: center; padding: 40px; color: var(--text-light); font-size: 0.85rem; border-top: 1px solid var(--border); margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <div class="branding">
        <img src="../Images/logo.jpg" class="logoimg" alt="Logo">
        <div class="branding-text">
            <h1>Student Support Agent – Admin</h1>
            <small>Academic Administration Portal</small>
        </div>
    </div>
</header>

<nav>
    <div class="nav-top">
        <a href="Admin-index.php">Dashboard</a>
        <a href="add_student.php" class="active">Manage Students</a>
        <a href="Admin-index.php?section=units">Manage Units</a>
        <a href="Admin-index.php?section=registrations">Registrations</a>
        <a href="../logout.php">Sign Out</a>
    </div>
</nav>

<div class="nav-sub">
    <a href="add_student.php">Add Student</a>
    <a href="view_students.php" class="active">Student Directory</a>
</div>

<div class="container">
    <div class="admin-strip">
        <span>Active Admin: <strong><?php echo htmlspecialchars($admin_name); ?></strong></span> 
        <span>Total Records Found: <strong style="color:var(--primary); font-size: 1.1rem;"><?php echo $current_view_count; ?></strong></span>
    </div>

    <form class="search-container" method="GET" action="view_students.php">
        <input type="text" name="search" class="search-input" placeholder="Search by name or Reg Number..." value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="dept">
            <option value="all">All Departments</option>
            <option value="Information Technology" <?php if($dept_filter == 'Information Technology') echo 'selected'; ?>>Information Technology</option>
            <option value="Computer Science" <?php if($dept_filter == 'Computer Science') echo 'selected'; ?>>Computer Science</option>
            <option value="Enterprise Computing" <?php if($dept_filter == 'Enterprise Computing') echo 'selected'; ?>>Enterprise Computing</option>
            <option value="Information Science & Knowledge Management" <?php if($dept_filter == 'Information Science & Knowledge Management') echo 'selected'; ?>>Information Science & Knowledge Management</option>
        </select>
        <button type="submit" class="btn-filter">Search & Filter</button>
        <a href="view_students.php" style="margin-left: 10px; font-size: 0.8rem; color: var(--text-light); text-decoration: none;">Reset</a>
    </form>

    <div class="section-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Registration No</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                if($current_view_count > 0):
                    while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $row['reg_number']; ?></strong></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><span class="dept-badge"><?php echo $row['department']; ?></span></td>
                        <td><?php echo $row['email']; ?></td>
                    </tr>
                <?php endwhile; 
                else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-light);">No student records found matching your criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    <p><strong>Department of Registrar (Academic)</strong></p>
    &copy; 2026 Portal Assistant AI | Mount Kenya University
</footer>

</body>
</html>