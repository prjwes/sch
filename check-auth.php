<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get user theme preference
$stmt = $pdo->prepare("SELECT theme FROM user_preferences WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$preference = $stmt->fetch();

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'user_role' => $_SESSION['user_role'],
        'email' => $_SESSION['email'],
        'theme' => $preference['theme'] ?? 'light'
    ]
]);
?>
