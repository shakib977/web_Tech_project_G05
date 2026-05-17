<?php
// controllers/StudentController.php
// ██████████████████████████████████████████
// MEMBER 1 — STUDENT
// ██████████████████████████████████████████

class StudentController {
    private $conn;
    private $uid;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->uid  = $_SESSION['user_id'];
    }

    // ── DASHBOARD ────────────────────────────────
    public function dashboard() {
        $uid = $this->uid;

        // Enrolled courses with next quiz
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.title, s.name AS subject, u.name AS instructor,
                    e.enrolled_at,
                    (SELECT COUNT(*) FROM quizzes WHERE course_id=c.id AND status='published') AS quiz_count,
                    (SELECT MIN(available_from) FROM quizzes
                     WHERE course_id=c.id AND status='published' AND available_from > NOW()) AS next_quiz
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             JOIN subjects s ON c.subject_id = s.id
             JOIN users   u ON c.instructor_id = u.id
             WHERE e.student_id = ? AND e.status = 'active'
             ORDER BY e.enrolled_at DESC LIMIT 6"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        // Stats
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=? AND status='active'");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($total_courses); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM attempts WHERE student_id=? AND completed_at IS NOT NULL");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($total_attempts); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT COALESCE(AVG(score),0) FROM attempts WHERE student_id=? AND completed_at IS NOT NULL");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($avg_score); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM attempts a JOIN quizzes q ON a.quiz_id=q.id
             WHERE a.student_id=? AND a.completed_at IS NOT NULL AND a.score >= q.pass_mark"
        );
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($passed); $stmt->fetch(); $stmt->close();

        // Recent attempts
        $stmt = $this->conn->prepare(
            "SELECT a.id, q.title AS quiz_title, c.title AS course_title, a.score, q.total_marks, q.pass_mark,
                    a.completed_at
             FROM attempts a
             JOIN quizzes q ON a.quiz_id = q.id
             JOIN courses c ON q.course_id = c.id
             WHERE a.student_id = ? AND a.completed_at IS NOT NULL
             ORDER BY a.completed_at DESC LIMIT 5"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $recent_attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/student/dashboard.php';
    }

    // ── BROWSE COURSES ───────────────────────────
    public function browseCourses() {
        $uid        = $this->uid;
        $subject_id = intval($_GET['subject_id'] ?? 0);
        $search     = trim($_GET['search'] ?? '');
        $enrolled_only = intval($_GET['enrolled_only'] ?? 0); // ← NEW
    
        $where  = ["c.status = 'active'"];
        $params = [];
        $types  = '';
    
        // ── Enrolled only filter ──────────────────────
        if ($enrolled_only) {
            $where[] = "EXISTS (
                SELECT 1 FROM enrollments e
                WHERE e.course_id=c.id AND e.student_id=? AND e.status='active'
            )";
            $params[] = $uid;
            $types   .= 'i';
        }
    
        if ($subject_id) {
            $where[]  = "c.subject_id = ?";
            $params[] = $subject_id;
            $types   .= 'i';
        }
    
        if ($search) {
            $like     = '%' . $search . '%';
            $where[]  = "(c.title LIKE ? OR c.description LIKE ? OR u.name LIKE ?)";
            $params   = array_merge($params, [$like, $like, $like]);
            $types   .= 'sss';
        }
    
        $sql = "SELECT c.id, c.title, c.description, c.enrollment_type, c.max_students,
                       s.name AS subject, u.name AS instructor,
                       (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count,
                       (SELECT id     FROM enrollments WHERE student_id={$uid} AND course_id=c.id LIMIT 1) AS my_enrollment_id,
                       (SELECT status FROM enrollments WHERE student_id={$uid} AND course_id=c.id LIMIT 1) AS my_status
                FROM courses c
                JOIN subjects s ON c.subject_id = s.id
                JOIN users   u ON c.instructor_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY c.created_at DESC";
    
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        $subjects = $this->conn->query("SELECT * FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        require 'views/student/browse_courses.php';
    }

    // ── COURSE DETAIL ────────────────────────────
    public function courseDetail() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        // Check enrollment
        $stmt = $this->conn->prepare("SELECT status FROM enrollments WHERE student_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute();
        $enroll = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$enroll || $enroll['status'] !== 'active') {
            header('Location: index.php?page=student&action=browse_courses'); exit;
        }

        // Course info
        $stmt = $this->conn->prepare(
            "SELECT c.*, 
        s.name AS subject, 
        u.name AS instructor, 
        u.email AS instructor_email,

        (SELECT COUNT(*) 
         FROM enrollments 
         WHERE course_id=c.id 
         AND status='active') AS enrolled_count

 FROM courses c 
 JOIN subjects s ON c.subject_id=s.id 
 JOIN users u ON c.instructor_id=u.id

 WHERE c.id = ?"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        // Assigned TA
        $stmt = $this->conn->prepare(
            "SELECT u.name, u.email FROM course_tas ct JOIN users u ON ct.ta_id=u.id WHERE ct.course_id=? LIMIT 1"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $ta = $stmt->get_result()->fetch_assoc(); $stmt->close();

        // Shows: ALL practice quizzes + graded quizzes not yet completed
$stmt = $this->conn->prepare(
    "SELECT q.*,
            (SELECT COUNT(*) FROM attempts a
             WHERE a.quiz_id=q.id AND a.student_id=? AND a.completed_at IS NOT NULL
            ) AS attempt_count,
            (SELECT MAX(a.score) FROM attempts a
             WHERE a.quiz_id=q.id AND a.student_id=? AND a.completed_at IS NOT NULL
            ) AS best_score
     FROM quizzes q
     WHERE q.course_id=? AND q.status='published'
       AND (
           q.quiz_type = 'practice'
           OR (
               q.quiz_type = 'graded'
               AND NOT EXISTS (
                   SELECT 1 FROM attempts a
                   WHERE a.quiz_id=q.id
                     AND a.student_id=?
                     AND a.completed_at IS NOT NULL
               )
           )
       )
     ORDER BY q.quiz_type DESC, q.available_from, q.id"
);
$stmt->bind_param('iiii', $uid, $uid, $course_id, $uid);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Separate: ALL published quizzes for leaderboard dropdown
$stmt = $this->conn->prepare(
    "SELECT id, title, quiz_type FROM quizzes
     WHERE course_id=? AND status='published'
     ORDER BY quiz_type DESC, title"
);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$all_quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

        // Announcements
        $stmt = $this->conn->prepare(
            "SELECT a.title, a.body, a.from_ta, a.created_at, u.name AS author
             FROM announcements a JOIN users u ON a.author_id=u.id
             WHERE a.course_id=? ORDER BY a.created_at DESC"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        // Materials
        $stmt = $this->conn->prepare(
            "SELECT * FROM course_materials WHERE course_id=? ORDER BY created_at DESC"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/student/course_detail.php';
    }

    // ── ENROLL (form submit fallback) ────────────
    public function enroll() {
        // Most enrollment done via AJAX (api/enroll.php)
        // This handles direct form submissions
        $course_id = intval($_POST['course_id'] ?? 0);
        $uid = $this->uid;
        if (!$course_id) { header('Location: index.php?page=student&action=browse_courses'); exit; }

        $stmt = $this->conn->prepare("SELECT enrollment_type FROM courses WHERE id=? AND status='active'");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$course) { header('Location: index.php?page=student&action=browse_courses'); exit; }

        $status = ($course['enrollment_type'] === 'open') ? 'active' : 'pending';
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO enrollments (student_id, course_id, status) VALUES (?,?,?)"
        );
        $stmt->bind_param('iis', $uid, $course_id, $status); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=student&action=browse_courses&msg=enrolled'); exit;
    }

    // ── TAKE QUIZ ────────────────────────────────
    public function takeQuiz() {
        $uid     = $this->uid;
        $quiz_id = intval($_GET['quiz_id'] ?? 0);

        // Get quiz + validate enrollment
        $stmt = $this->conn->prepare(
            "SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id=c.id WHERE q.id=?"
        );
        $stmt->bind_param('i', $quiz_id); $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc(); $stmt->close();

        if (!$quiz || $quiz['status'] !== 'published') {
            header('Location: index.php?page=student&action=dashboard'); exit;
        }

        // Check enrollment
        $stmt = $this->conn->prepare(
            "SELECT id FROM enrollments WHERE student_id=? AND course_id=? AND status='active'"
        );
        $stmt->bind_param('ii', $uid, $quiz['course_id']); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { header('Location: index.php?page=student&action=dashboard'); exit; }
        $stmt->close();

        // Graded quiz: check existing incomplete attempt
        $attempt_id = null;
        if ($quiz['quiz_type'] === 'graded') {
            $stmt = $this->conn->prepare(
                "SELECT id FROM attempts WHERE quiz_id=? AND student_id=? AND completed_at IS NULL ORDER BY started_at DESC LIMIT 1"
            );
            $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc(); $stmt->close();

            if ($row) { $attempt_id = $row['id']; }
            else {
                $stmt = $this->conn->prepare("INSERT INTO attempts (quiz_id, student_id) VALUES (?,?)");
                $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute();
                $attempt_id = $this->conn->insert_id; $stmt->close();
            }
        } else {
            // Practice: always new attempt
            $stmt = $this->conn->prepare("INSERT INTO attempts (quiz_id, student_id) VALUES (?,?)");
            $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute();
            $attempt_id = $this->conn->insert_id; $stmt->close();
        }

        // Get questions + options
        $stmt = $this->conn->prepare(
            "SELECT q.id,
                    q.question_text,
                    q.marks,
                    q.order_index
             FROM questions q
             WHERE q.quiz_id=?
             ORDER BY q.order_index, q.id"
        );
        
        $stmt->bind_param('i', $quiz_id);
        
        $stmt->execute();
        
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT q.id, q.question_text, q.marks, q.order_index FROM questions q WHERE q.quiz_id=? ORDER BY q.order_index, q.id"
        );
        $stmt->bind_param('i', $quiz_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        foreach ($questions as $key => $q) {
            $stmt = $this->conn->prepare("SELECT id, option_text FROM options WHERE question_id=?");
            $stmt->bind_param('i', $q['id']); $stmt->execute();
            $questions[$key]['options'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        
            $stmt = $this->conn->prepare("SELECT selected_option_id FROM answers WHERE attempt_id=? AND question_id=?");
            $stmt->bind_param('ii', $attempt_id, $q['id']); $stmt->execute();
            $ans = $stmt->get_result()->fetch_assoc(); $stmt->close();
            $questions[$key]['selected'] = $ans['selected_option_id'] ?? null;
        }

        // Calculate elapsed time (for graded)
        $stmt = $this->conn->prepare("SELECT TIMESTAMPDIFF(SECOND, started_at, NOW()) FROM attempts WHERE id=?");
        $stmt->bind_param('i', $attempt_id); $stmt->execute(); $stmt->bind_result($elapsed); $stmt->fetch(); $stmt->close();

        $time_remaining = ($quiz['time_limit_minutes'] * 60) - intval($elapsed);
        if ($time_remaining < 0) $time_remaining = 0;

        require 'views/student/quiz_take.php';
    }

    // ── SUBMIT QUIZ ──────────────────────────────
    public function submitQuiz() {
        $uid        = $this->uid;
        $attempt_id = intval($_POST['attempt_id'] ?? 0);
        $quiz_id    = intval($_POST['quiz_id'] ?? 0);

        // Validate attempt belongs to user
        $stmt = $this->conn->prepare("SELECT id FROM attempts WHERE id=? AND student_id=? AND completed_at IS NULL");
        $stmt->bind_param('ii', $attempt_id, $uid); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { header('Location: index.php?page=student&action=dashboard'); exit; }
        $stmt->close();

        // Get questions
        $stmt = $this->conn->prepare("SELECT id, marks FROM questions WHERE quiz_id=?");
        $stmt->bind_param('i', $quiz_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $total_score = 0;
        foreach ($questions as $q) {
            $q_id     = $q['id'];
            $selected = intval($_POST['q_' . $q_id] ?? 0);

            // Save / update answer
            $stmt = $this->conn->prepare(
                "INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE selected_option_id=?"
            );
            $null = null;
            $opt  = $selected ?: null;
            $stmt->bind_param('iiii', $attempt_id, $q_id, $opt, $opt); $stmt->execute(); $stmt->close();

            // Check if correct
            if ($selected) {
                $stmt = $this->conn->prepare("SELECT is_correct FROM options WHERE id=? AND question_id=?");
                $stmt->bind_param('ii', $selected, $q_id); $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc(); $stmt->close();
                if ($res && $res['is_correct']) $total_score += $q['marks'];
            }
        }

        // Update attempt
        $stmt = $this->conn->prepare(
            "UPDATE attempts SET score=?, completed_at=NOW(), is_graded=1 WHERE id=?"
        );
        $stmt->bind_param('di', $total_score, $attempt_id); $stmt->execute(); $stmt->close();

        header('Location: index.php?page=student&action=quiz_result&attempt_id=' . $attempt_id); exit;
    }

    // ── QUIZ RESULT ──────────────────────────────
    public function quizResult() {
        $uid        = $this->uid;
        $attempt_id = intval($_GET['attempt_id'] ?? 0);

        $stmt = $this->conn->prepare(
            "SELECT a.*, q.title, q.total_marks, q.pass_mark, q.quiz_type, c.title AS course_title, c.id AS course_id
             FROM attempts a JOIN quizzes q ON a.quiz_id=q.id JOIN courses c ON q.course_id=c.id
             WHERE a.id=? AND a.student_id=?"
        );
        $stmt->bind_param('ii', $attempt_id, $uid); $stmt->execute();
        $attempt = $stmt->get_result()->fetch_assoc(); $stmt->close();

        if (!$attempt) { header('Location: index.php?page=student&action=dashboard'); exit; }

        // Question breakdown
        $stmt = $this->conn->prepare(
            "SELECT qu.id, qu.question_text, qu.marks,
                    an.selected_option_id,
                    (SELECT option_text FROM options WHERE id=an.selected_option_id) AS selected_text,
                    (SELECT is_correct FROM options WHERE id=an.selected_option_id) AS is_correct,
                    (SELECT id FROM options WHERE question_id=qu.id AND is_correct=1 LIMIT 1) AS correct_option_id,
                    (SELECT option_text FROM options WHERE question_id=qu.id AND is_correct=1 LIMIT 1) AS correct_text
             FROM questions qu
             LEFT JOIN answers an ON an.question_id=qu.id AND an.attempt_id=?
             WHERE qu.quiz_id=? ORDER BY qu.order_index, qu.id"
        );
        $stmt->bind_param('ii', $attempt_id, $attempt['quiz_id']); $stmt->execute();
        $breakdown = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/student/quiz_result.php';
    }

    // ── ATTEMPT HISTORY ──────────────────────────
    public function attemptHistory() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $where  = 'a.student_id = ?';
        $params = [$uid]; $types = 'i';
        if ($course_id) { $where .= ' AND q.course_id = ?'; $params[] = $course_id; $types .= 'i'; }

        $stmt = $this->conn->prepare(
            "SELECT a.id, a.score, a.started_at, a.completed_at,
                    q.title AS quiz_title, q.total_marks, q.pass_mark, q.quiz_type,
                    c.title AS course_title
             FROM attempts a
             JOIN quizzes q ON a.quiz_id = q.id
             JOIN courses c ON q.course_id = c.id
             WHERE {$where} AND a.completed_at IS NOT NULL
             ORDER BY a.completed_at DESC"
        );
        $stmt->bind_param($types, ...$params); $stmt->execute();
        $attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        // For dropdown filter
        $enrolled_courses = $this->conn->prepare(
            "SELECT c.id, c.title FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE e.student_id=? AND e.status='active'"
        );
        $enrolled_courses->bind_param('i', $uid); $enrolled_courses->execute();
        $enrolled = $enrolled_courses->get_result()->fetch_all(MYSQLI_ASSOC); $enrolled_courses->close();

        require 'views/student/attempt_history.php';
    }

    // ── PROFILE ──────────────────────────────────
    public function profile() {
        $uid = $this->uid;
        $error = $success = '';

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc(); $stmt->close();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['form_type'] ?? '';

            if ($type === 'profile') {
                $name  = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $prog  = trim($_POST['program'] ?? '');
                if (!$name) { $error = 'Name cannot be empty.'; }
                else {
                    $stmt = $this->conn->prepare("UPDATE users SET name=?, phone=?, program=? WHERE id=?");
                    $stmt->bind_param('sssi', $name, $phone, $prog, $uid); $stmt->execute(); $stmt->close();
                    $_SESSION['user_name'] = $name;
                    $success = 'Profile updated successfully.';
                    $user['name'] = $name; $user['phone'] = $phone; $user['program'] = $prog;
                }
            } elseif ($type === 'password') {
                $current = $_POST['current_password'] ?? '';
                $newpass = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';
                if (!password_verify($current, $user['password_hash'])) $error = 'Current password is incorrect.';
                elseif (strlen($newpass) < 8)  $error = 'New password must be 8+ characters.';
                elseif ($newpass !== $confirm)  $error = 'Passwords do not match.';
                else {
                    $hash = password_hash($newpass, PASSWORD_BCRYPT);
                    $stmt = $this->conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
                    $stmt->bind_param('si', $hash, $uid); $stmt->execute(); $stmt->close();
                    $success = 'Password changed successfully.';
                }
            } elseif ($type === 'photo') {
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
                    $f   = $_FILES['profile_pic'];
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png','gif','webp'];
                    if (!in_array($ext, $allowed)) $error = 'Only image files allowed.';
                    elseif ($f['size'] > 2 * 1024 * 1024) $error = 'Image must be under 2MB.';
                    else {
                        $filename = 'profile_' . $uid . '_' . time() . '.' . $ext;
                        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                        if (move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $filename)) {
                            $stmt = $this->conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                            $stmt->bind_param('si', $filename, $uid); $stmt->execute(); $stmt->close();
                            $_SESSION['profile_pic'] = $filename;
                            $user['profile_pic'] = $filename;
                            $success = 'Profile picture updated.';
                        } else { $error = 'Failed to upload image.'; }
                    }
                }
                elseif ($type === 'password') {
                    $current = $_POST['current_password'] ?? '';
                    $newpass = $_POST['new_password']     ?? '';
                    $confirm = $_POST['confirm_password'] ?? '';
                
                    if (!password_verify($current, $user['password_hash'])) {
                        $error = '❌ Incorrect current password. Please try again.';
                    } elseif (strlen($newpass) < 8) {
                        $error = '❌ New password must be at least 8 characters.';
                    } elseif (!preg_match('/[A-Z]/', $newpass)) {
                        $error = '❌ New password must contain at least one capital letter.';
                    } elseif ($newpass !== $confirm) {
                        $error = '❌ Password mismatch — new passwords do not match.';
                    } else {
                        $hash = password_hash($newpass, PASSWORD_BCRYPT);
                        $stmt = $this->conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
                        $stmt->bind_param('si', $hash, $uid); $stmt->execute(); $stmt->close();
                        $success = '✅ Password changed successfully.';
                    }
                }
            }
        }

        require 'views/student/profile.php';
    }

    // ── PERFORMANCE DASHBOARD ────────────────────
    public function performance() {
        $uid = $this->uid;
    
        // ── Overall stats — average as % ─────────────
        $stmt = $this->conn->prepare(
            "SELECT
                COUNT(*) AS total,
                ROUND(AVG(CASE WHEN q.total_marks > 0
                          THEN (a.score / q.total_marks * 100)
                          ELSE 0 END), 1) AS avg_pct,
                SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) AS passed
             FROM attempts a
             JOIN quizzes q ON a.quiz_id = q.id
             WHERE a.student_id=? AND a.completed_at IS NOT NULL"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $overall = $stmt->get_result()->fetch_assoc(); $stmt->close();
    
        // ── TOP scores per quiz ───────────────────────
        $stmt = $this->conn->prepare(
            "SELECT
                q.id AS quiz_id,
                q.title AS quiz_title,
                q.total_marks,
                q.pass_mark,
                c.title AS course_title,
                MAX(a.score) AS best_score,
                ROUND(MAX(a.score) / q.total_marks * 100, 1) AS score_pct,
                COUNT(a.id) AS attempt_count
             FROM attempts a
             JOIN quizzes q ON a.quiz_id = q.id
             JOIN courses c ON q.course_id = c.id
             WHERE a.student_id=? AND a.completed_at IS NOT NULL
               AND q.total_marks > 0
             GROUP BY q.id
             ORDER BY score_pct DESC
             LIMIT 15"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $top_scores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    
        // ── Per-subject average % ─────────────────────
        $stmt = $this->conn->prepare(
            "SELECT s.name AS subject,
                    ROUND(AVG(a.score / q.total_marks * 100), 1) AS avg_pct,
                    COUNT(*) AS attempt_count
             FROM attempts a
             JOIN quizzes q ON a.quiz_id=q.id
             JOIN courses c ON q.course_id=c.id
             JOIN subjects s ON c.subject_id=s.id
             WHERE a.student_id=? AND a.completed_at IS NOT NULL AND q.total_marks > 0
             GROUP BY s.id ORDER BY avg_pct DESC"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $by_subject = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    
        require 'views/student/performance.php';
    }
    // ── Q&A BOARD ────────────────────────────────
    public function qaBoard() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        // Verify enrollment
        if ($course_id) {
            $stmt = $this->conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=? AND status='active'");
            $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
            if (!$stmt->num_rows) { header('Location: index.php?page=student&action=dashboard'); exit; }
            $stmt->close();
        }

        $stmt = $this->conn->prepare(
            "SELECT qq.*, u.name AS student_name
             FROM qa_questions qq JOIN users u ON qq.student_id=u.id
             WHERE qq.course_id=? ORDER BY qq.is_resolved ASC, qq.created_at DESC"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        foreach ($questions as &$q) {
            $stmt = $this->conn->prepare(
                "SELECT qa.*, u.name AS author_name, u.role AS author_role
                 FROM qa_answers qa JOIN users u ON qa.author_id=u.id
                 WHERE qa.qa_question_id=? ORDER BY qa.is_endorsed DESC, qa.created_at ASC"
            );
            $stmt->bind_param('i', $q['id']); $stmt->execute();
            $q['answers'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        }

        // Course title
        $stmt = $this->conn->prepare("SELECT title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        require 'views/student/qa_board.php';
    }

    // ── POST Q&A QUESTION ────────────────────────
    public function postQuestion() {
        $uid       = $this->uid;
        $course_id = intval($_POST['course_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $body      = trim($_POST['body']  ?? '');

        if ($course_id && $title && $body) {
            $stmt = $this->conn->prepare(
                "INSERT INTO qa_questions(course_id, student_id, title, body) VALUES (?,?,?,?)"
            );
            $stmt->bind_param('iiss', $course_id, $uid, $title, $body); $stmt->execute(); $stmt->close();
            }
            header('Location: index.php?page=student&action=qa_board&course_id=' . $course_id); exit;
            }

        // ── MARK RESOLVED ────────────────────────────
public function markResolved() {
    $uid = $this->uid;
    $qid = intval($_GET['q_id'] ?? 0);
    $cid = intval($_GET['course_id'] ?? 0);
    $stmt = $this->conn->prepare("UPDATE qa_questions SET is_resolved=1 WHERE id=? AND student_id=?");
    $stmt->bind_param('ii', $qid, $uid); $stmt->execute(); $stmt->close();
    header('Location: index.php?page=student&action=qa_board&course_id=' . $cid); exit;
}

// ── DOUBT SESSIONS ───────────────────────────
public function doubtSessions() {
    $uid = $this->uid;

    // Get enrolled course IDs
    $stmt = $this->conn->prepare("SELECT course_id FROM enrollments WHERE student_id=? AND status='active'");
    $stmt->bind_param('i', $uid); $stmt->execute();
    $rows     = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    $cids_arr = array_column($rows, 'course_id');

    $sessions = [];
    if ($cids_arr) {
        $placeholders = implode(',', array_fill(0, count($cids_arr), '?'));
        $types        = str_repeat('i', count($cids_arr));
        $stmt = $this->conn->prepare(
            "SELECT ds.*, c.title AS course_title, u.name AS ta_name,
                    (SELECT id FROM doubt_session_bookings WHERE doubt_session_id=ds.id AND student_id=?) AS my_booking_id,
                    (SELECT COUNT(*) FROM doubt_session_bookings WHERE doubt_session_id=ds.id) AS booking_count
             FROM doubt_sessions ds
             JOIN courses c ON ds.course_id=c.id
             JOIN users u ON ds.ta_id=u.id
             WHERE ds.course_id IN ({$placeholders}) AND ds.is_cancelled=0 AND ds.scheduled_at > NOW()
             ORDER BY ds.scheduled_at ASC"
        );
        $params = array_merge([$uid], $cids_arr);
        $stmt->bind_param('i' . $types, ...$params); $stmt->execute();
        $sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    }

    // My booked sessions (including past)
    $stmt = $this->conn->prepare(
        "SELECT ds.*, c.title AS course_title, u.name AS ta_name, b.booked_at
         FROM doubt_session_bookings b
         JOIN doubt_sessions ds ON b.doubt_session_id=ds.id
         JOIN courses c ON ds.course_id=c.id
         JOIN users u ON ds.ta_id=u.id
         WHERE b.student_id=? ORDER BY ds.scheduled_at DESC"
    );
    $stmt->bind_param('i', $uid); $stmt->execute();
    $my_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

    require 'views/student/doubt_sessions.php';
}

// ── BOOK SESSION ─────────────────────────────
public function bookSession() {
    $uid        = $this->uid;
    $session_id = intval($_POST['session_id'] ?? 0);

    // Check capacity
    $stmt = $this->conn->prepare(
        "SELECT ds.max_attendees,
                (SELECT COUNT(*) FROM doubt_session_bookings WHERE doubt_session_id=ds.id) AS booked
         FROM doubt_sessions ds WHERE ds.id=?"
    );
    $stmt->bind_param('i', $session_id); $stmt->execute();
    $ds = $stmt->get_result()->fetch_assoc(); $stmt->close();

    if ($ds && $ds['booked'] < $ds['max_attendees']) {
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO doubt_session_bookings (doubt_session_id, student_id) VALUES (?,?)"
        );
        $stmt->bind_param('ii', $session_id, $uid); $stmt->execute(); $stmt->close();
    }
    header('Location: index.php?page=student&action=doubt_sessions&msg=booked'); exit;
}

// ── DROP COURSE ──────────────────────────────
public function dropCourse() {
    $uid       = $this->uid;
    $course_id = intval($_POST['course_id'] ?? 0);

    $stmt = $this->conn->prepare(
        "SELECT COUNT(*) FROM attempts a
         JOIN quizzes q ON a.quiz_id=q.id
         WHERE a.student_id=? AND q.course_id=?
           AND q.quiz_type='graded' AND a.completed_at IS NOT NULL"
    );
    $stmt->bind_param('ii', $uid, $course_id);
    $stmt->execute(); $stmt->bind_result($count); $stmt->fetch(); $stmt->close();

    if ($count > 0) {
        // Cannot drop — redirect back with error
        header('Location: index.php?page=student&action=course_detail&course_id=' . $course_id . '&drop_error=1');
    } else {
        $stmt = $this->conn->prepare(
            "UPDATE enrollments SET status='dropped' WHERE student_id=? AND course_id=?"
        );
        $stmt->bind_param('ii', $uid, $course_id);
        $stmt->execute(); $stmt->close();
        header('Location: index.php?page=student&action=dashboard&msg=dropped');
    }
    exit;
}

public function qaCourses() {
    $uid = $this->uid;
    $stmt = $this->conn->prepare(
        "SELECT c.id, c.title, s.name AS subject,
                (SELECT COUNT(*) FROM qa_questions WHERE course_id=c.id AND student_id=?) AS my_questions,
                (SELECT COUNT(*) FROM qa_questions WHERE course_id=c.id) AS total_questions
         FROM enrollments e
         JOIN courses c ON e.course_id=c.id
         JOIN subjects s ON c.subject_id=s.id
         WHERE e.student_id=? AND e.status='active'
         ORDER BY c.title"
    );
    $stmt->bind_param('ii', $uid, $uid); $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    require 'views/student/qa_courses.php';
}
}