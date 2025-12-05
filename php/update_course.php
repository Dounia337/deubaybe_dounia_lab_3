<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $course_id = $input['course_id'];
    $course_name = $input['course_name'];
    $semester = $input['semester'];
    $cohort = $input['cohort'];
    $teacher_id = $_SESSION['user_id'];
    
    // Verify the teacher owns this course
    $verify_query = "SELECT * FROM courses WHERE id = $course_id AND created_by = $teacher_id";
    $verify_result = $connection->query($verify_query);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: You can only edit your own courses']);
        exit();
    }
    
    // Update course
    $sql = "UPDATE courses SET course_name = '$course_name', semester = '$semester', cohort = '$cohort' WHERE id = $course_id AND created_by = $teacher_id";
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>