<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $course_id = $input['course_id'];
    $teacher_id = $_SESSION['user_id'];
    
    // Verify the teacher owns this course
    $verify_query = "SELECT * FROM courses WHERE id = $course_id AND created_by = $teacher_id";
    $verify_result = $connection->query($verify_query);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: You can only delete your own courses']);
        exit();
    }
    
    // Delete associated attendance records first
    $delete_attendance = "DELETE a FROM attendance a 
                         JOIN sessions s ON a.session_id = s.id 
                         WHERE s.course_id = $course_id";
    $connection->query($delete_attendance);
    
    // Delete associated sessions
    $delete_sessions = "DELETE FROM sessions WHERE course_id = $course_id";
    $connection->query($delete_sessions);
    
    // Delete enrollments
    $delete_enrollments = "DELETE FROM enrollments WHERE course_id = $course_id";
    $connection->query($delete_enrollments);
    
    // Finally delete the course
    $sql = "DELETE FROM courses WHERE id = $course_id AND created_by = $teacher_id";
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Course and all associated data deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>