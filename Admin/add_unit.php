<?php
/* ==========================
   DATABASE CONNECTION
========================== */
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$day_of_week = $data['day_of_week'] ?? '';
$time_from = $data['time_from'] ?? '';
$time_to = $data['time_to'] ?? '';
$venue = $data['venue'] ?? '';
$lecturer = $data['lecturer'] ?? '';
$semester = $data['semester'] ?? '';
$academic_year = $data['academic_year'] ?? '';
$exclude_unit = $data['unit_code'] ?? '';

// ========== VALIDATE REQUIRED FIELDS ==========
if (empty($day_of_week)) {
    echo json_encode([
        'has_conflicts' => false,
        'conflicts' => [],
        'message' => 'Please select a day of the week.'
    ]);
    exit;
}

if (empty($time_from) || empty($time_to)) {
    echo json_encode([
        'has_conflicts' => false,
        'conflicts' => [],
        'message' => 'Please select both start and end times.'
    ]);
    exit;
}

// ========== VALIDATE TIME ORDER ==========
function convertTimeToMinutes($time) {
    if (empty($time)) return 0;
    $parts = explode(':', $time);
    return (intval($parts[0]) * 60) + intval($parts[1]);
}

function timeOverlaps($start1, $end1, $start2, $end2) {
    $start1_min = convertTimeToMinutes($start1);
    $end1_min = convertTimeToMinutes($end1);
    $start2_min = convertTimeToMinutes($start2);
    $end2_min = convertTimeToMinutes($end2);
    return ($start1_min < $end2_min && $start2_min < $end1_min);
}

// Check if end time is after start time
if (convertTimeToMinutes($time_from) >= convertTimeToMinutes($time_to)) {
    echo json_encode([
        'has_conflicts' => true,
        'conflicts' => [['type' => 'time', 'message' => 'End time must be after start time.']],
        'message' => '<strong>⚠️ Invalid Time:</strong><br>• End time must be after start time.<br><br>Please correct the time.'
    ]);
    exit;
}

$conflicts = [];

// ========== CHECK VENUE CONFLICTS ==========
if (!empty($venue)) {
    $query = "SELECT unit_code, course_title, time_from, time_to 
              FROM timetable 
              WHERE venue = ? 
              AND day_of_week = ? 
              AND semester = ? 
              AND academic_year = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $venue, $day_of_week, $semester, $academic_year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['unit_code'] != $exclude_unit && timeOverlaps($time_from, $time_to, $row['time_from'], $row['time_to'])) {
            $conflicts[] = [
                'type' => 'venue',
                'item' => $venue,
                'day' => $day_of_week,
                'conflicting_unit' => $row['unit_code'],
                'conflicting_title' => $row['course_title'],
                'conflicting_time' => $row['time_from'] . ' - ' . $row['time_to'],
                'message' => "Venue already booked for {$row['unit_code']} on {$day_of_week} at {$row['time_from']} - {$row['time_to']}"
            ];
        }
    }
    $stmt->close();
}

// ========== CHECK LECTURER CONFLICTS ==========
if (!empty($lecturer)) {
    $query = "SELECT unit_code, course_title, time_from, time_to 
              FROM timetable 
              WHERE lecturer = ? 
              AND day_of_week = ? 
              AND semester = ? 
              AND academic_year = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $lecturer, $day_of_week, $semester, $academic_year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['unit_code'] != $exclude_unit && timeOverlaps($time_from, $time_to, $row['time_from'], $row['time_to'])) {
            $conflicts[] = [
                'type' => 'lecturer',
                'item' => $lecturer,
                'day' => $day_of_week,
                'conflicting_unit' => $row['unit_code'],
                'conflicting_title' => $row['course_title'],
                'conflicting_time' => $row['time_from'] . ' - ' . $row['time_to'],
                'message' => "Lecturer already teaching {$row['unit_code']} on {$day_of_week} at {$row['time_from']} - {$row['time_to']}"
            ];
        }
    }
    $stmt->close();
}

// ========== BUILD RESPONSE MESSAGE ==========
$message = '';
if (!empty($conflicts)) {
    $message = '<strong>⚠️ Conflicts Detected on ' . htmlspecialchars($day_of_week) . ':</strong><br><br>';
    foreach ($conflicts as $conflict) {
        if ($conflict['type'] == 'venue') {
            $message .= "📍 <strong>Venue Conflict:</strong> '{$conflict['item']}'<br>";
            $message .= "   └─ {$conflict['message']}<br><br>";
        } elseif ($conflict['type'] == 'lecturer') {
            $message .= "👨‍🏫 <strong>Lecturer Conflict:</strong> '{$conflict['item']}'<br>";
            $message .= "   └─ {$conflict['message']}<br><br>";
        } elseif ($conflict['type'] == 'time') {
            $message .= "⏰ <strong>Time Error:</strong> {$conflict['message']}<br><br>";
        }
    }
    $message .= '💡 <strong>Suggestions:</strong><br>';
    $message .= '• Try a different time slot on the same day<br>';
    $message .= '• Try a different day of the week<br>';
    $message .= '• Choose a different venue or lecturer<br>';
} else {
    // No conflicts - show success message
    $message = '<strong>✅ No Conflicts Detected!</strong><br><br>';
    $message .= '✓ Venue <strong>' . htmlspecialchars($venue) . '</strong> is available on ' . htmlspecialchars($day_of_week) . ' at this time<br>';
    if (!empty($lecturer)) {
        $message .= '✓ Lecturer <strong>' . htmlspecialchars($lecturer) . '</strong> is available on ' . htmlspecialchars($day_of_week) . ' at this time<br>';
    }
    $message .= '<br>You can safely schedule this unit.';
}

// ========== RETURN RESPONSE ==========
echo json_encode([
    'has_conflicts' => !empty($conflicts),
    'conflicts' => $conflicts,
    'message' => $message,
    'debug' => [
        'day_of_week' => $day_of_week,
        'time_from' => $time_from,
        'time_to' => $time_to,
        'venue' => $venue,
        'lecturer' => $lecturer,
        'semester' => $semester,
        'academic_year' => $academic_year
    ]
]);

$conn->close();
?>