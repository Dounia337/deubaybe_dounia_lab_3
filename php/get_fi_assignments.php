<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    
    $teacher_id = $_SESSION['user_id'];
    
    // Get all FI assignments for teacher's courses
    $query = "SELECT ca.id, ca.course_id, ca.assistant_id, c.course_name, 
              CONCAT(u.first_name, ' ', u.last_name) as fi_name,
              DATE_FORMAT(ca.assigned_at, '%M %d, %Y') as assigned_at
              FROM course_assistants ca
              JOIN courses c ON ca.course_id = c.id
              JOIN users u ON ca.assistant_id = u.id
              WHERE c.created_by = $teacher_id
              ORDER BY ca.assigned_at DESC";
    
    $result = $connection->query($query);
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $assignments]);
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>