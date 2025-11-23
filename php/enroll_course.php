<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
    
    $student_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];
    
    // Check if already enrolled

    $check_sql = "SELECT id FROM enrollments WHERE student_id = $student_id AND course_id = $course_id";
    $result = $connection->query($check_sql);
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course']);
    } 
    
    else {
        // Enroll student
        $enroll_sql = "INSERT INTO enrollments (student_id, course_id) VALUES ($student_id, $course_id)";
        
        if ($connection->query($enroll_sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Successfully enrolled in course']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error enrolling in course: ' . $connection->error]);
        }
    }
} else 

{
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>