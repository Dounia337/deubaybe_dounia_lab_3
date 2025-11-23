<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $course_name = $input['course_name'];
    $semester = $input['semester'];
    $cohort = $input['cohort'];
    $created_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO courses (course_name, semester, cohort, created_by) VALUES ('$course_name', '$semester', '$cohort', $created_by)";
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Course created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>