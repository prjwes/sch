-- School Management System Database Schema
CREATE DATABASE IF NOT EXISTS school_management;
USE school_management;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_role ENUM('Admin', 'DoS_Social_Affairs', 'Finance', 'DoS_Exam', 'Teacher', 'Student') NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    reset_code VARCHAR(4) DEFAULT NULL,
    reset_code_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_number VARCHAR(10) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    age INT NOT NULL,
    grade ENUM('7', '8', '9') NOT NULL,
    passport_photo VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'graduated') DEFAULT 'active',
    graduation_year YEAR DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exams table
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(255) NOT NULL,
    grade ENUM('7', '8', '9') NOT NULL,
    term ENUM('1', '2', '3') NOT NULL,
    year YEAR NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Exam marks table
CREATE TABLE IF NOT EXISTS exam_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    subject ENUM('English', 'Kiswahili', 'Math', 'Integrated Science', 'CRE', 'Social Studies', 'Pretechnical Studies', 'Agriculture', 'C&A') NOT NULL,
    marks INT NOT NULL,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_exam_student_subject (exam_id, student_id, subject)
);

-- Fee types table
CREATE TABLE IF NOT EXISTS fee_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    grade ENUM('7', '8', '9') NOT NULL,
    term ENUM('1', '2', '3') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_type_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    term ENUM('1', '2', '3') NOT NULL,
    year YEAR NOT NULL,
    recorded_by INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Clubs table
CREATE TABLE IF NOT EXISTS clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_name VARCHAR(255) NOT NULL,
    description TEXT,
    mission TEXT,
    vision TEXT,
    club_image VARCHAR(255) DEFAULT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Club members table
CREATE TABLE IF NOT EXISTS club_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    student_id INT NOT NULL,
    joined_date DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_club_member (club_id, student_id)
);

-- Club posts table
CREATE TABLE IF NOT EXISTS club_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    posted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id)
);

-- Club comments table
CREATE TABLE IF NOT EXISTS club_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES club_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Club likes table
CREATE TABLE IF NOT EXISTS club_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES club_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_like (post_id, user_id)
);

-- Notes table
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subject ENUM('English', 'Kiswahili', 'Math', 'Integrated Science', 'CRE', 'Social Studies', 'Pretechnical Studies', 'Agriculture', 'C&A') NOT NULL,
    grade ENUM('7', '8', '9') NOT NULL,
    content TEXT,
    file_path VARCHAR(255),
    file_type ENUM('text', 'image', 'video', 'pdf', 'document') NOT NULL,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- User preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme ENUM('light', 'dark') DEFAULT 'light',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_pref (user_id)
);

-- Insert default fee types
INSERT INTO fee_types (fee_name, amount, grade, term) VALUES
('Tuition Fee', 15000.00, '7', '1'),
('Lunch Fee', 5000.00, '7', '1'),
('School Fee', 8000.00, '7', '1'),
('Sports Fee', 2000.00, '7', '1'),
('Uniform Fee', 3000.00, '7', '1'),
('Tuition Fee', 15000.00, '8', '1'),
('Lunch Fee', 5000.00, '8', '1'),
('School Fee', 8000.00, '8', '1'),
('Sports Fee', 2000.00, '8', '1'),
('Uniform Fee', 3000.00, '8', '1'),
('Tuition Fee', 15000.00, '9', '1'),
('Lunch Fee', 5000.00, '9', '1'),
('School Fee', 8000.00, '9', '1'),
('Sports Fee', 2000.00, '9', '1'),
('Uniform Fee', 3000.00, '9', '1');
