<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$required_fields = ['fullName', 'username', 'email', 'phone', 'userRole', 'password'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field $field is required"]);
        exit();
    }
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Check role limits
$role_limits = [
    'Admin' => 2,
    'DoS_Social_Affairs' => 2,
    'Finance' => 2
];

if (isset($role_limits[$input['userRole']])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_role = ?");
    $stmt->execute([$input['userRole']]);
    $count = $stmt->fetchColumn();
    
    if ($count >= $role_limits[$input['userRole']]) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Maximum number of {$input['userRole']} users reached"]);
        exit();
    }
}

try {
    // Check for existing username, email, or phone
    $stmt = $pdo->prepare("SELECT username, email, phone FROM users WHERE username = ? OR email = ? OR phone = ?");
    $stmt->execute([$input['username'], $input['email'], $input['phone']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['username'] === $input['username']) {
            throw new Exception('Username already exists');
        }
        if ($existing['email'] === $input['email']) {
            throw new Exception('Email already exists');
        }
        if ($existing['phone'] === $input['phone']) {
            throw new Exception('Phone number already exists');
        }
    }
    
    // Hash password
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, username, email, phone, password, user_role) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['fullName'],
        $input['username'],
        $input['email'],
        $input['phone'],
        $hashedPassword,
        $input['userRole']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
