<?php

ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isOwner()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Owner only.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'resetPassword':
        handleResetPassword();
        break;
    case 'deleteUser':
        handleDeleteUser();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleResetPassword() {
    global $conn;
    
    $userId = $_POST['user_id'] ?? null;
    $newPassword = $_POST['new_password'] ?? '';
    
    if (!$userId || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID and new password are required']);
        exit;
    }
    
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }
        
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDeleteUser() {
    global $conn;
    
    $userId = $_POST['user_id'] ?? null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required']);
        exit;
    }
    
    // Prevent owner from deleting themselves
    if ((int)$userId === (int)$_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'You cannot delete your own owner account']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
