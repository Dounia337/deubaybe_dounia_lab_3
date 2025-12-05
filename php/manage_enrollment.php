<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && ($_SESSION['user_role'] === 'teacher' || $_SESSION['user_role'] === 'fI')) {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $enrollment_id = $input['enrollment_id'];
    $action = $input['action']; // 'approve' or 'reject'
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];
    
    // Get enrollment details
    $enrollment_query = "SELECT e.*, c.created_by FROM enrollments e
                        JOIN courses c ON e.course_id = c.id
                        WHERE e.id = $enrollment_id";
    $enrollment_result = $connection->query($enrollment_query);
    
    if ($enrollment_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
        exit();
    }
    
    $enrollment = $enrollment_result->fetch_assoc();
    $course_id = $enrollment['course_id'];
    
    // Verify authorization
    if ($user_role === 'teacher') {
        // Teacher must own the course
        if ($enrollment['created_by'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Not your course']);
            exit();
        }
    } else if ($user_role === 'fI') {
        // FI must be assigned to the course
        $fi_check = "SELECT * FROM course_assistants WHERE course_id = $course_id AND assistant_id = $user_id";
        $fi_result = $connection->query($fi_check);
        if ($fi_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Not assigned to this course']);
            exit();
        }
    }
    
    if ($action === 'approve') {
        $sql = "UPDATE enrollments SET status = 'approved', approved_by = $user_id, approved_at = NOW() WHERE id = $enrollment_id";
        $message = 'Enrollment approved successfully';
    } else if ($action === 'reject') {
        $sql = "UPDATE enrollments SET status = 'rejected', approved_by = $user_id, approved_at = NOW() WHERE id = $enrollment_id";
        $message = 'Enrollment rejected';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }
    
    if ($connection->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $connection->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$connection->close();
?>