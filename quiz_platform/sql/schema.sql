CREATE DATABASE IF NOT EXISTS quiz_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quiz_platform;

-- =============================================
-- SHARED TABLES (All members use these)
-- =============================================

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT '',
    role ENUM('student','instructor','ta','admin') NOT NULL DEFAULT 'student',
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    student_id VARCHAR(50) DEFAULT '',
    program VARCHAR(100) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    subject_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    enrollment_type ENUM('open','approval') DEFAULT 'open',
    max_students INT DEFAULT 100,
    status ENUM('draft','active','archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE course_tas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    ta_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (ta_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('pending','active','dropped') DEFAULT 'pending',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    created_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    time_limit_minutes INT DEFAULT 30,
    total_marks INT DEFAULT 100,
    pass_mark INT DEFAULT 50,
    quiz_type ENUM('graded','practice') DEFAULT 'graded',
    status ENUM('draft','published') DEFAULT 'draft',
    available_from DATETIME,
    available_until DATETIME,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    marks INT DEFAULT 1,
    order_index INT DEFAULT 0,
    created_by INT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    score DECIMAL(8,2) DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    is_graded TINYINT(1) DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT,
    FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES options(id) ON DELETE SET NULL
);

CREATE TABLE course_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(255),
    material_type ENUM('document','link','video') DEFAULT 'document',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    author_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT,
    from_ta TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE qa_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE qa_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    qa_question_id INT NOT NULL,
    author_id INT NOT NULL,
    body TEXT NOT NULL,
    is_endorsed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (qa_question_id) REFERENCES qa_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE doubt_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    ta_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    scheduled_at DATETIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    location_or_link VARCHAR(500),
    max_attendees INT DEFAULT 20,
    is_cancelled TINYINT(1) DEFAULT 0,
    cancel_reason TEXT,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (ta_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE doubt_session_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doubt_session_id INT NOT NULL,
    student_id INT NOT NULL,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doubt_session_id) REFERENCES doubt_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (doubt_session_id, student_id)
);

CREATE TABLE platform_announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE platform_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value VARCHAR(500),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE integrity_flags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reported_by INT,
    course_id INT,
    user_id INT,
    reason TEXT,
    status ENUM('pending','resolved','escalated') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- SEED DATA
-- =============================================

-- Default admin (password: password)
INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('Platform Admin', 'admin@quiz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Default subjects
INSERT INTO subjects (name, description) VALUES
('Computer Science', 'Algorithms, data structures, programming'),
('Mathematics', 'Calculus, algebra, statistics'),
('Physics', 'Classical mechanics, electromagnetism, quantum'),
('English', 'Writing, literature, communication'),
('Database Systems', 'SQL, NoSQL, ER modeling');

-- Platform settings
INSERT INTO platform_settings (setting_key, setting_value) VALUES
('max_quiz_duration', '180'),
('max_students_per_course', '200'),
('platform_name', 'QuizPro'),
('at_risk_threshold', '50');









-- fix ids

UPDATE users SET student_id = CONCAT('st-',  id)  WHERE role='student'    AND (student_id='' OR student_id IS NULL);
UPDATE users SET student_id = CONCAT('ins-', id)  WHERE role='instructor' AND (student_id='' OR student_id IS NULL);
UPDATE users SET student_id = CONCAT('ta-',  id)  WHERE role='ta'         AND (student_id='' OR student_id IS NULL);
UPDATE users SET student_id = CONCAT('ad-',  id)  WHERE role='admin'      AND (student_id='' OR student_id IS NULL);











-- Fix student IDs
SET @n = 0;
UPDATE users SET student_id = CONCAT('st-', (@n := @n + 1))
WHERE role = 'student' ORDER BY id ASC;

-- Fix instructor IDs
SET @n = 0;
UPDATE users SET student_id = CONCAT('ins-', (@n := @n + 1))
WHERE role = 'instructor' ORDER BY id ASC;

-- Fix TA IDs
SET @n = 0;
UPDATE users SET student_id = CONCAT('ta-', (@n := @n + 1))
WHERE role = 'ta' ORDER BY id ASC;

-- Fix admin IDs
SET @n = 0;
UPDATE users SET student_id = CONCAT('ad-', (@n := @n + 1))
WHERE role = 'admin' ORDER BY id ASC;