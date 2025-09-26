<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['marks']) || !is_array($input['marks'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Marks data is required']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Prepare statement for inserting/updating marks
    $stmt = $pdo->prepare("
        INSERT INTO exam_marks (exam_id, student_id, subject, marks) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE marks = VALUES(marks)
    ");
    
    foreach ($input['marks'] as $mark) {
        if (empty($mark['examId']) || empty($mark['studentId']) || empty($mark['subject']) || !isset($mark['marks'])) {
            continue;
        }
        
        $stmt->execute([
            $mark['examId'],
            $mark['studentId'],
            $mark['subject'],
            $mark['marks']
        ]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Exam marks saved successfully'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Failed to save marks: ' . $e->getMessage()]);
}
?>
