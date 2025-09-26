<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    // Get total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE status = 'active'");
    $totalStudents = $stmt->fetchColumn();
    
    // Get students by grade
    $stmt = $pdo->query("SELECT grade, COUNT(*) as count FROM students WHERE status = 'active' GROUP BY grade");
    $gradeStats = $stmt->fetchAll();
    
    $grade7Count = 0;
    $grade8Count = 0;
    $grade9Count = 0;
    
    foreach ($gradeStats as $stat) {
        switch ($stat['grade']) {
            case '7':
                $grade7Count = $stat['count'];
                break;
            case '8':
                $grade8Count = $stat['count'];
                break;
            case '9':
                $grade9Count = $stat['count'];
                break;
        }
    }
    
    // Get total exams
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM exams WHERE year = YEAR(NOW())");
    $totalExams = $stmt->fetchColumn();
    
    // Get total clubs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clubs");
    $totalClubs = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'totalStudents' => $totalStudents,
            'totalExams' => $totalExams,
            'totalClubs' => $totalClubs,
            'grade7Count' => $grade7Count,
            'grade8Count' => $grade8Count,
            'grade9Count' => $grade9Count
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load dashboard stats']);
}
?>
