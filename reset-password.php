<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$required_fields = ['email', 'resetCode', 'newPassword'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field $field is required"]);
        exit();
    }
}

try {
    // Verify reset code
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE email = ? AND reset_code = ? AND reset_code_expires > NOW()
    ");
    $stmt->execute([$input['email'], $input['resetCode']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired reset code']);
        exit();
    }
    
    // Update password and clear reset code
    $hashedPassword = password_hash($input['newPassword'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = ?, reset_code = NULL, reset_code_expires = NULL 
        WHERE id = ?
    ");
    $stmt->execute([$hashedPassword, $user['id']]);
    
    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
}
?>
