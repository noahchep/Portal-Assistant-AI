<?php
/* ==========================
    DATABASE CONNECTION FIX
   ========================== */
if (!isset($conn)) {
    $db_path = $_SERVER['DOCUMENT_ROOT'] . '/Portal-Assistant-AI/db_connect.php';
    if (file_exists($db_path)) {
        include_once($db_path);
    } else {
        $conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
    }
}

if (!$conn) {
    die("<div style='color:red; padding:20px;'>Database connection failed.</div>");
}

/* ==========================
    DATA ANALYSIS LOGIC
   ========================== */

// 1. Fetch Total Responses
$total_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM survey_responses");
$total_count = mysqli_fetch_assoc($total_res)['total'] ?? 0;

// 2. Challenge Analysis
$challenges_q = mysqli_query($conn, "SELECT challenge_type, COUNT(*) as count FROM survey_responses GROUP BY challenge_type");

// 3. AI Performance Data (WEIGHTED ACCURACY)
$accuracy_q = mysqli_query($conn, "SELECT chatbot_help, COUNT(*) as count FROM survey_responses GROUP BY chatbot_help");

$total_weighted_score = 0;
while($row = mysqli_fetch_assoc($accuracy_q)) {
    $label = $row['chatbot_help'];
    $count = $row['count'];

    // Define weights based on your 25% intervals requirement
    if ($label == 'Highly Accurate') { $weight = 1.0; }  // 100%
    elseif ($label == 'Helpful')      { $weight = 0.75; } // 75%
    elseif ($label == 'Average')      { $weight = 0.50; } // 50%
    elseif ($label == 'Inaccurate')   { $weight = 0.25; } // 25%
    else                              { $weight = 0.0; }  // 0%

    $total_weighted_score += ($weight * $count);
}

// Final Accuracy Calculation
$final_accuracy = ($total_count > 0) ? round(($total_weighted_score / $total_count) * 100) : 0;

// 4. Mean Satisfaction Calculation
$avg_q = mysqli_query($conn, "SELECT AVG(ease_rating) as average FROM survey_responses");
$avg_score = ($total_count > 0) ? round(mysqli_fetch_assoc($avg_q)['average'], 2) : 0;

/* ==========================
    RESEARCH CONCLUSION GENERATOR
   ========================== */
$conclusion = "No data collected yet.";
if ($total_count > 0) {
    if ($avg_score >= 4) {
        $conclusion = "The <strong>Portal-Assistant-AI</strong> demonstrates high effectiveness. With an average rating of $avg_score/5, the system successfully provides autonomous academic guidance to students.";
    } elseif ($avg_score >= 2.5) {
        $conclusion = "The <strong>Portal-Assistant-AI</strong> shows moderate success. While students find the UI functional, the AI model requires further training on specific academic parameters to reach peak accuracy.";
    } else {
        $conclusion = "Current feedback for <strong>Portal-Assistant-AI</strong> indicates significant technical gaps. Future iterations should focus on improving NLP response accuracy and system integration.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-card h4 { margin: 0; color: #64748b; font-size: 0.8rem; text-transform: uppercase; }
        .stat-card .value { font-size: 1.8rem; font-weight: 800; color: #4f46e5; margin: 10px 0; }
        
        .analysis-container { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .conclusion-box { background: #f0f9ff; border-left: 5px solid #0ea5e9; padding: 20px; margin-top: 20px; border-radius: 0 8px 8px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        
        .section-header { margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
    </style>
</head>
<body>

    <div class="section-header">
        <h2 style="margin:0; color:#1e293b;">Portal-Assistant-AI: Analytics Dashboard</h2>
        <p style="margin:5px 0 0 0; color:#64748b; font-size:0.9rem;">Evaluation of Chatbot Effectiveness</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h4>Sample Size (N)</h4>
            <div class="value"><?php echo $total_count; ?></div>
        </div>
        <div class="stat-card">
            <h4>Mean Satisfaction</h4>
            <div class="value"><?php echo $avg_score; ?>/5</div>
        </div>
        <div class="stat-card">
            <h4>AI Accuracy Rate</h4>
            <div class="value">
                <?php echo $final_accuracy; ?>%
            </div>
        </div>
    </div>

    <div class="analysis-container">
        <h3 style="font-size: 1rem; color: #475569; margin-top: 0;">Frequency Distribution: System Challenges</h3>
        <table>
            <thead>
                <tr>
                    <th>Challenge Category</th>
                    <th>Frequency (f)</th>
                    <th>Percentage (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($total_count > 0) {
                    mysqli_data_seek($challenges_q, 0); 
                    while($row = mysqli_fetch_assoc($challenges_q)): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['challenge_type']); ?></td>
                    <td><?php echo $row['count']; ?></td>
                    <td><?php echo round(($row['count'] / $total_count) * 100); ?>%</td>
                </tr>
                <?php 
                    endwhile; 
                } else {
                    echo "<tr><td colspan='3' style='text-align:center;'>No survey data available for Portal-Assistant-AI yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="conclusion-box">
            <h4 style="margin-top:0; color:#0369a1;">Automated Research Conclusion</h4>
            <p style="margin-bottom:0; color:#0c4a6e; line-height:1.6;">
                <?php echo $conclusion; ?>
            </p>
        </div>
    </div>

</body>
</html>