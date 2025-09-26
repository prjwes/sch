<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['fullName']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Full name and password are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE full_name = ? AND status = 'active'");
    $stmt->execute([$input['fullName']]);
    $student = $stmt->fetch();
    
    if (!$student) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Student not found or inactive']);
        exit();
    }
    
    // Check password (default format: student.{graduation_year})
    $currentYear = date('Y');
    $graduationYear = $currentYear + (10 - intval($student['grade'])); // Grade 7->3 years, 8->2 years, 9->1 year
    $defaultPassword = "student." . $graduationYear;
    
    $passwordValid = false;
    if ($student['password']) {
        // Custom password set
        $passwordValid = password_verify($input['password'], $student['password']);
    } else {
        // Default password
        $passwordValid = ($input['password'] === $defaultPassword);
    }
    
    if (!$passwordValid) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
        exit();
    }
    
    // Set session
    $_SESSION['student_id'] = $student['id'];
    $_SESSION['student_name'] = $student['full_name'];
    $_SESSION['student_grade'] = $student['grade'];
    $_SESSION['admission_number'] = $student['admission_number'];
    $_SESSION['is_student'] = true;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful',
        'student' => [
            'id' => $student['id'],
            'full_name' => $student['full_name'],
            'admission_number' => $student['admission_number'],
            'grade' => $student['grade'],
            'gender' => $student['gender']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed']);
}
?>
