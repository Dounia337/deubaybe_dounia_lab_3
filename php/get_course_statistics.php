<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher' && isset($_GET['course_id'])) {
    
    $course_id = $_GET['course_id'];
    $teacher_id = $_SESSION['user_id'];
    
    // Verify the teacher owns this course
    $verify_query = "SELECT * FROM courses WHERE id = $course_id AND created_by = $teacher_id";
    $verify_result = $connection->query($verify_query);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    // Get total sessions
    $sessions_query = "SELECT COUNT(*) as total FROM sessions WHERE course_id = $course_id";
    $sessions_result = $connection->query($sessions_query);
    $sessions = $sessions_result->fetch_assoc();
    
    // Get total enrolled students
    $students_query = "SELECT COUNT(*) as total FROM enrollments WHERE course_id = $course_id";
    $students_result = $connection->query($students_query);
    $students = $students_result->fetch_assoc();
    
    // Get attendance statistics
    $attendance_query = "SELECT 
        COUNT(*) as total_records,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
        FROM attendance a
        JOIN sessions s ON a.session_id = s.id
        WHERE s.course_id = $course_id";
    $attendance_result = $connection->query($attendance_query);
    $attendance = $attendance_result->fetch_assoc();
    
    // Calculate average attendance rate
    $avg_attendance = 0;
    if ($attendance['total_records'] > 0) {
        $avg_attendance = round((($attendance['present_count'] + $attendance['late_count']) / $attendance['total_records']) * 100, 2);
    }
    
    $data = [
        'total_sessions' => $sessions['total'],
        'total_students' => $students['total'],
        'total_attendance_records' => $attendance['total_records'],
        'present_count' => $attendance['present_count'],
        'late_count' => $attendance['late_count'],
        'avg_attendance' => $avg_attendance
    ];
    
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$connection->close();
?>