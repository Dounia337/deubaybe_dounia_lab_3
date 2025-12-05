<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'fI') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $course_id = $input['course_id'];
    $session_date = $input['session_date'];
    $session_time = $input['session_time'];
    $hall = $input['hall'];
    $duration = $input['duration'];
    $session_pin = $input['session_pin'];
    $created_by = $_SESSION['user_id'];
    
    // Validate PIN is 4 digits
    if (!preg_match('/^\d{4}$/', $session_pin)) {
        echo json_encode(['success' => false, 'message' => 'PIN must be exactly 4 digits']);
        exit();
    }
    
    // Deactivate all other active sessions for this course
    $deactivate_sql = "UPDATE sessions SET is_active = FALSE WHERE course_id = $course_id AND is_active = TRUE";
    $connection->query($deactivate_sql);
    
    // Create new session
    $sql = "INSERT INTO sessions (course_id, session_date, session_time, hall, session_pin, duration_minutes, is_active, created_by) 
            VALUES ($course_id, '$session_date', '$session_time', '$hall', '$session_pin', $duration, TRUE, $created_by)";
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Session created successfully with PIN: ' . $session_pin]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>