<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$examId = $_GET['examId'] ?? '';

if (empty($examId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Exam ID is required']);
    exit();
}

try {
    // Check if user has permission to delete (Admin or DoS_Exam or creator)
    $stmt = $pdo->prepare("SELECT created_by FROM exams WHERE id = ?");
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        throw new Exception('Exam not found');
    }
    
    $canDelete = hasRole(['Admin', 'DoS_Exam']) || $exam['created_by'] == $_SESSION['user_id'];
    
    if (!$canDelete) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this exam']);
        exit();
    }
    
    // Delete exam (marks will be deleted automatically due to foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
    $stmt->execute([$examId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Exam deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
