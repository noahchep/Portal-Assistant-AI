<?php
/* ==========================
   DATABASE CONNECTION & LOGIC
========================== */
include_once('db_connect.php');
if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
}

/* SEARCH & FILTER LOGIC */
$search = $_GET['search'] ?? '';
$year_filter = $_GET['year_level'] ?? 'all';

// Base Query
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

<form class="search-container" method="GET" action="Admin-index.php" style="background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <input type="hidden" name="section" value="units">
    
    <div style="flex-grow: 1;">
        <input type="text" name="search" style="width: 200px; padding: 10px; border: 1px solid var(--border); border-radius: 8px;" 
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
    
    <?php if(!empty($search) || $year_filter !== 'all'): ?>
        <a href="Admin-index.php?section=units" style="font-size: 0.8rem; color: var(--text-light); text-decoration: none;">Reset</a>
    <?php endif; ?>
</form>

<div class="section-box">
    <div style="padding: 15px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0; font-size: 1.1rem; color: var(--primary);">Academic Year Workload (Master Plan)</h2>
        <span style="font-size: 0.8rem; color: var(--text-light);">Showing <strong><?php echo $count; ?></strong> Units</span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Unit Code</th>
                    <th>Unit Name</th>
                    <th>Year Level</th>
                    <th>Semester</th>
                    <th>Offering Time</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($count > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $badgeStyle = ($row['offering_time'] == 'Once a Year') 
                            ? "background: #fee2e2; color: #991b1b;" 
                            : "background: #dcfce7; color: #166534;";
                        
                        echo "<tr>
                                <td><span style='background: var(--accent); color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;'>{$row['unit_code']}</span></td>
                                <td style='font-weight: 500;'>{$row['unit_name']}</td>
                                <td>{$row['year_level']}</td>
                                <td>{$row['semester_level']}</td>
                                <td><span style='padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; $badgeStyle'>{$row['offering_time']}</span></td>
                                <td style='text-align: center;'>
                                    <a href='Admin-index.php?section=units&delete_id={$row['id']}' 
                                       style='color: #ef4444; text-decoration: none; font-weight: bold; font-size: 0.8rem;'
                                       onclick=\"return confirm('Remove this unit from the Master Plan?')\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 40px; color: var(--text-light);'>No workload units found matching your criteria.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>