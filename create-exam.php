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

$required_fields = ['examName', 'examGrade', 'examTerm', 'examYear'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field $field is required"]);
        exit();
    }
}

try {
    // Check if exam already exists
    $stmt = $pdo->prepare("
        SELECT id FROM exams 
        WHERE exam_name = ? AND grade = ? AND term = ? AND year = ?
    ");
    $stmt->execute([
        $input['examName'],
        $input['examGrade'],
        $input['examTerm'],
        $input['examYear']
    ]);
    
    if ($stmt->fetch()) {
        throw new Exception('An exam with this name already exists for the selected grade, term, and year');
    }
    
    // Create exam
    $stmt = $pdo->prepare("
        INSERT INTO exams (exam_name, grade, term, year, created_by) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['examName'],
        $input['examGrade'],
        $input['examTerm'],
        $input['examYear'],
        $_SESSION['user_id']
    ]);
    
    $examId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Exam created successfully',
        'examId' => $examId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
