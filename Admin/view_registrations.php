<?php
/* ===========================================
    PORTAL-ASSISTANT-AI: ENHANCED REGISTRATIONS
   =========================================== */

// 1. Handle Status Confirmation & Deletion
if (isset($_GET['action']) && isset($_GET['reg_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['reg_id']);
    
    if ($_GET['action'] == 'confirm') {
        mysqli_query($conn, "UPDATE registered_courses SET status = 'Confirmed' WHERE id = '$id'");
    } elseif ($_GET['action'] == 'drop') {
        mysqli_query($conn, "DELETE FROM registered_courses WHERE id = '$id'");
    }
    
    echo "<script>window.location.href='Admin-index.php?section=registrations';</script>";
}

// 2. Filter Logic
$where_clause = "WHERE 1=1";
if (isset($_POST['filter_reg'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_reg']);
    $where_clause .= " AND (student_reg_no LIKE '%$search%' OR unit_code LIKE '%$search%')";
}

$query = "SELECT * FROM registered_courses $where_clause ORDER BY registered_at DESC";
$result = mysqli_query($conn, $query);
?>

<style>
    .master-plan-container {
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-family: 'Segoe UI', sans-serif;
    }

    .plan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    .plan-header h2 { color: #4f46e5; font-size: 1.1rem; font-weight: 700; margin: 0; }

    .workload-table { width: 100%; border-collapse: collapse; }

    .workload-table th {
        text-align: left;
        padding: 12px 15px;
        color: #334155;
        font-weight: 700;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.85rem;
    }

    .workload-table td {
        padding: 15px;
        border-bottom: 1px solid #f8fafc;
        color: #475569;
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .unit-badge {
        background: #eef2ff;
        color: #4338ca;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        border: 1px solid #e0e7ff;
    }

    .status-tag {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .confirmed { background: #dcfce7; color: #166534; }
    .provisional { background: #fee2e2; color: #991b1b; }

    /* Action Buttons */
    .btn-action {
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        transition: 0.2s;
    }

    .btn-confirm { background: #4f46e5; color: white; margin-right: 5px; }
    .btn-confirm:hover { background: #3730a3; }

    .btn-drop { background: #ef4444; color: white; }
    .btn-drop:hover { background: #dc2626; }
    
    .academic-info { color: #1e293b; font-weight: 500; }
    .semester-info { display: block; font-size: 0.7rem; color: #64748b; margin-top: 2px; }
</style>

<div class="master-plan-container">
    <div class="plan-header">
        <h2>Student Registration Master Plan</h2>
        <form method="POST" style="display: flex; gap: 8px;">
            <input type="text" name="search_reg" placeholder="Search Reg No or Unit..." 
                   style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem;">
            <button type="submit" name="filter_reg" class="btn-action btn-confirm" style="border:none; cursor:pointer;">Filter View</button>
        </form>
    </div>

    <table class="workload-table">
        <thead>
            <tr>
                <th>Unit Code</th>
                <th>Registration Number</th>
                <th>Academic Level & Semester</th>
                <th>Registration Status</th>
                <th>Administrative Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><span class="unit-badge"><?php echo htmlspecialchars($row['unit_code']); ?></span></td>
                    <td style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($row['student_reg_no']); ?></td>
                    <td>
                        <span class="academic-info">Year <?php echo htmlspecialchars($row['academic_year']); ?></span>
                        <span class="semester-info"><?php echo htmlspecialchars($row['semester']); ?></span>
                    </td>
                    <td>
                        <span class="status-tag <?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if($row['status'] == 'Provisional'): ?>
                            <a href="Admin-index.php?section=registrations&action=confirm&reg_id=<?php echo $row['id']; ?>" 
                               class="btn-action btn-confirm">+ Confirm</a>
                        <?php endif; ?>
                        
                        <a href="Admin-index.php?section=registrations&action=drop&reg_id=<?php echo $row['id']; ?>" 
                           class="btn-action btn-drop" 
                           onclick="return confirm('Are you sure you want to drop this unit for student <?php echo $row['student_reg_no']; ?>?');">
                            × Drop Unit
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding: 30px; color: #94a3b8;">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>