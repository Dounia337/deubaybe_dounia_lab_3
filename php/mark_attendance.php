<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $session_id = $input['session_id'];
    $pin = $input['pin'];
    $student_id = $_SESSION['user_id'];
    
    // Validate PIN format
    if (!preg_match('/^\d{4}$/', $pin)) {
        echo json_encode(['success' => false, 'message' => 'Invalid PIN format']);
        exit();
    }
    
    // Verify session exists and is active
    $session_query = "SELECT * FROM sessions WHERE id = $session_id AND is_active = TRUE";
    $session_result = $connection->query($session_query);
    
    if ($session_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Session is not active']);
        exit();
    }
    
    $session = $session_result->fetch_assoc();
    
    // Verify PIN
    if ($session['session_pin'] !== $pin) {
        echo json_encode(['success' => false, 'message' => 'Incorrect PIN']);
        exit();
    }
    
    // Check if student is enrolled in the course and approved
    $enrollment_check = "SELECT * FROM enrollments WHERE student_id = $student_id AND course_id = {$session['course_id']} AND status = 'approved'";
    $enrollment_result = $connection->query($enrollment_check);
    
    if ($enrollment_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course or enrollment is pending approval']);
        exit();
    }
    
    // Check if attendance already marked
    $attendance_check = "SELECT * FROM attendance WHERE session_id = $session_id AND student_id = $student_id";
    $attendance_result = $connection->query($attendance_check);
    
    if ($attendance_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Attendance already marked for this session']);
        exit();
    }
    
    // Calculate if late
    $current_time = new DateTime();
    $session_time = new DateTime($session['session_date'] . ' ' . $session['session_time']);
    $grace_period = $session['duration_minutes'] ?? 60;
    $late_threshold = clone $session_time;
    $late_threshold->modify('+15 minutes'); // 15 minutes grace period
    
    $status = 'present';
    if ($current_time > $late_threshold) {
        $status = 'late';
    }
    
    // Mark attendance
    $insert_sql = "INSERT INTO attendance (session_id, student_id, status, time_marked, marked_by) 
                   VALUES ($session_id, $student_id, '$status', NOW(), $student_id)";
    
    if ($connection->query($insert_sql) === TRUE) {
        $status_msg = ($status === 'late') ? 'Attendance marked as LATE' : 'Attendance marked as PRESENT';
        echo json_encode(['success' => true, 'message' => $status_msg]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error marking attendance: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>