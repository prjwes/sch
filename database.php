<?php
// Database configuration
$host = 'localhost';
$dbname = 'school_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function hasRole($roles) {
    if (!isLoggedIn()) return false;
    if (is_string($roles)) $roles = [$roles];
    return in_array($_SESSION['user_role'], $roles);
}

function generateAdmissionNumber($pdo) {
    $stmt = $pdo->query("SELECT MAX(CAST(admission_number AS UNSIGNED)) as max_adm FROM students");
    $result = $stmt->fetch();
    $nextNumber = ($result['max_adm'] ?? 0) + 1;
    return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}

function sendEmail($to, $subject, $message) {
    // Simple email function - in production, use PHPMailer or similar
    $headers = "From: noreply@school.com\r\n";
    $headers .= "Reply-To: noreply@school.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>
