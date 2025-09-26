<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$grade = $_GET['grade'] ?? '';

if (empty($grade)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Grade parameter is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT id, admission_number, full_name, gender, age, grade, passport_photo 
        FROM students 
        WHERE grade = ? AND status = 'active' 
        ORDER BY admission_number ASC
    ");
    $stmt->execute([$grade]);
    $students = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load students']);
}
?>
