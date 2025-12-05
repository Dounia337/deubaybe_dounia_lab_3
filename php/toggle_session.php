<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'fI') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $session_id = $input['session_id'];
    $action = $input['action'];
    
    if ($action === 'activate') {
        // Deactivate all other sessions first
        $deactivate_sql = "UPDATE sessions SET is_active = FALSE WHERE is_active = TRUE";
        $connection->query($deactivate_sql);
        
        // Activate this session
        $sql = "UPDATE sessions SET is_active = TRUE WHERE id = $session_id";
        $message = 'Session activated successfully';
    } else {
        // Deactivate session
        $sql = "UPDATE sessions SET is_active = FALSE WHERE id = $session_id";
        $message = 'Session deactivated successfully';
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