<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
    
    $student_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];
    
    // Check if already enrolled or pending
    $check_sql = "SELECT id, status FROM enrollments WHERE student_id = $student_id AND course_id = $course_id";
    $result = $connection->query($check_sql);
    
    if ($result->num_rows > 0) {
        $enrollment = $result->fetch_assoc();
        
        if ($enrollment['status'] === 'pending') {
            echo json_encode(['success' => false, 'message' => 'Your enrollment request is already pending approval']);
        } else if ($enrollment['status'] === 'approved') {
            echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course']);
        } else if ($enrollment['status'] === 'rejected') {
            // Allow re-enrollment if previously rejected
            $update_sql = "UPDATE enrollments SET status = 'pending', enrolled_at = NOW(), approved_by = NULL, approved_at = NULL WHERE id = {$enrollment['id']}";
            if ($connection->query($update_sql) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Enrollment request submitted. Awaiting approval from faculty.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error submitting request: ' . $connection->error]);
            }
        }
    } else {
        // Create new pending enrollment
        $enroll_sql = "INSERT INTO enrollments (student_id, course_id, status) VALUES ($student_id, $course_id, 'pending')";
        
        if ($connection->query($enroll_sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Enrollment request submitted successfully! Awaiting approval from faculty.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting enrollment request: ' . $connection->error]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>