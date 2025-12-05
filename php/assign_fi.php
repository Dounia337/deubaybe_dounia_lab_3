<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $course_id = $input['course_id'];
    $fi_id = $input['fi_id'];
    $teacher_id = $_SESSION['user_id'];
    
    // Verify the teacher owns this course
    $verify_query = "SELECT * FROM courses WHERE id = $course_id AND created_by = $teacher_id";
    $verify_result = $connection->query($verify_query);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: You can only assign FI to your own courses']);
        exit();
    }
    
    // Verify the FI user exists and has the FI role
    $fi_check = "SELECT * FROM users WHERE id = $fi_id AND role = 'fI'";
    $fi_result = $connection->query($fi_check);
    
    if ($fi_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid Faculty Intern']);
        exit();
    }
    
    // Check if already assigned
    $check_query = "SELECT * FROM course_assistants WHERE course_id = $course_id AND assistant_id = $fi_id";
    $check_result = $connection->query($check_query);
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This FI is already assigned to this course']);
        exit();
    }
    
    // Assign FI to course
    $sql = "INSERT INTO course_assistants (course_id, assistant_id, assigned_by) VALUES ($course_id, $fi_id, $teacher_id)";
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Faculty Intern assigned successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>