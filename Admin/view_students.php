<?php
/* ==========================
   DATABASE CONNECTION
   We use include_once to prevent errors if already connected in index
========================== */
include_once('db_connect.php');

// Fallback connection if index variable is different
if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
}

/* ==========================
   SEARCH & FILTER LOGIC
========================== */
$search = $_GET['search'] ?? '';
$dept_filter = $_GET['dept'] ?? 'all';

// Base Query
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
$current_view_count = ($result) ? mysqli_num_rows($result) : 0;
?>

<form class="search-container" method="GET" action="Admin-index.php" style="background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; display: flex; gap: 15px; align-items: center;">
    <input type="hidden" name="section" value="students">
    
    <input type="text" name="search" style="flex-grow: 1; padding: 10px; border: 1px solid var(--border); border-radius: 8px;" placeholder="Search by name or Reg Number..." value="<?php echo htmlspecialchars($search); ?>">
    
    <select name="dept" style="padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
        <option value="all">All Departments</option>
        <option value="Information Technology" <?php if($dept_filter == 'Information Technology') echo 'selected'; ?>>Information Technology</option>
        <option value="Computer Science" <?php if($dept_filter == 'Computer Science') echo 'selected'; ?>>Computer Science</option>
        <option value="Enterprise Computing" <?php if($dept_filter == 'Enterprise Computing') echo 'selected'; ?>>Enterprise Computing</option>
        <option value="Information Science & Knowledge Management" <?php if($dept_filter == 'Information Science & Knowledge Management') echo 'selected'; ?>>Information Science & Knowledge Management</option>
    </select>
    
    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Search</button>
    <a href="Admin-index.php?section=students" style="font-size: 0.8rem; color: var(--text-light); text-decoration: none;">Reset</a>
</form>

<div class="section-box">
    <div style="padding: 15px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0; font-size: 1.1rem;">Student Directory</h2>
        <span style="font-size: 0.8rem; color: var(--text-light);">Records found: <strong><?php echo $current_view_count; ?></strong></span>
    </div>
    <div style="overflow-x: auto;">
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
                        <td><strong><?php echo htmlspecialchars($row['reg_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><span class="dept-badge" style="background: var(--accent); color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.75rem;"><?php echo htmlspecialchars($row['department']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                    </tr>
                <?php endwhile; 
                else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-light);">No student records found matching your criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>