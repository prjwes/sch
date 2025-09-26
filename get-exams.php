<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$grade = $_GET['grade'] ?? '';
$sortBy = $_GET['sortBy'] ?? 'created_at';
$sortOrder = $_GET['sortOrder'] ?? 'DESC';

if (empty($grade)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Grade parameter is required']);
    exit();
}

// Validate sort parameters
$allowedSortBy = ['created_at', 'exam_name', 'term', 'year'];
$allowedSortOrder = ['ASC', 'DESC'];

if (!in_array($sortBy, $allowedSortBy)) {
    $sortBy = 'created_at';
}

if (!in_array($sortOrder, $allowedSortOrder)) {
    $sortOrder = 'DESC';
}

try {
    $stmt = $pdo->prepare("
        SELECT id, exam_name, grade, term, year, created_at
        FROM exams 
        WHERE grade = ? 
        ORDER BY $sortBy $sortOrder
    ");
    $stmt->execute([$grade]);
    $exams = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'exams' => $exams
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load exams']);
}
?>
