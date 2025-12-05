<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $assignment_id = $input['assignment_id'];
    $teacher_id = $_SESSION['user_id'];
    
    // Verify the teacher owns the course for this assignment
    $verify_query = "SELECT ca.* FROM course_assistants ca
                     JOIN courses c ON ca.course_id = c.id
                     WHERE ca.id = $assignment_id AND c.created_by = $teacher_id";
    $verify_result = $connection->query($verify_query);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    // Remove assignment
    $sql = "DELETE FROM course_assistants WHERE id = $assignment_id";
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'FI removed from course']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>