<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit();
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No account found with this email']);
        exit();
    }
    
    // Generate 4-digit reset code
    $resetCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Save reset code
    $stmt = $pdo->prepare("UPDATE users SET reset_code = ?, reset_code_expires = ? WHERE id = ?");
    $stmt->execute([$resetCode, $expiresAt, $user['id']]);
    
    // Send email (simplified version)
    $subject = "Password Reset Code - School Management System";
    $message = "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>Hello {$user['full_name']},</p>
            <p>You requested a password reset. Your 4-digit reset code is:</p>
            <h3 style='color: #2563eb; font-size: 24px; letter-spacing: 2px;'>{$resetCode}</h3>
            <p>This code will expire in 1 hour.</p>
            <p>If you didn't request this reset, please ignore this email.</p>
        </body>
        </html>
    ";
    
    if (sendEmail($input['email'], $subject, $message)) {
        echo json_encode(['success' => true, 'message' => 'Reset code sent to your email']);
    } else {
        throw new Exception('Failed to send email');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send reset code']);
}
?>
