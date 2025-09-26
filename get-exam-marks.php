<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$examId = $_GET['examId'] ?? '';

if (empty($examId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Exam ID is required']);
    exit();
}

try {
    // Get exam details
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        throw new Exception('Exam not found');
    }
    
    // Get exam marks with student details
    $stmt = $pdo->prepare("
        SELECT 
            em.student_id,
            em.subject,
            em.marks,
            s.admission_number,
            s.full_name
        FROM exam_marks em
        JOIN students s ON em.student_id = s.id
        WHERE em.exam_id = ?
        ORDER BY s.admission_number ASC
    ");
    $stmt->execute([$examId]);
    $marks = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'exam' => $exam,
        'marks' => $marks
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
