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

$required_fields = ['studentName', 'studentGender', 'studentAge', 'studentGrade'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field $field is required"]);
        exit();
    }
}

try {
    // Generate admission number
    $admissionNumber = generateAdmissionNumber($pdo);
    
    // Handle file upload
    $photoPath = null;
    if (isset($_FILES['studentPhoto']) && $_FILES['studentPhoto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/students/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['studentPhoto']['name'], PATHINFO_EXTENSION);
        $fileName = $admissionNumber . '_' . time() . '.' . $fileExtension;
        $photoPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($_FILES['studentPhoto']['tmp_name'], $photoPath)) {
            throw new Exception('Failed to upload photo');
        }
        
        $photoPath = 'uploads/students/' . $fileName; // Store relative path
    }
    
    // Generate default password
    $currentYear = date('Y');
    $graduationYear = $currentYear + (10 - intval($_POST['studentGrade']));
    $defaultPassword = "student." . $graduationYear;
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    // Insert student
    $stmt = $pdo->prepare("
        INSERT INTO students (admission_number, full_name, gender, age, grade, passport_photo, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $admissionNumber,
        $_POST['studentName'],
        $_POST['studentGender'],
        $_POST['studentAge'],
        $_POST['studentGrade'],
        $photoPath,
        $hashedPassword
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Student added successfully',
        'admission_number' => $admissionNumber
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
