<?php


class InstructorController {
    private $conn;
    private $uid;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->uid  = $_SESSION['user_id'];
    }

    private function myCoursesIds() {
        $stmt = $this->conn->prepare("SELECT id FROM courses WHERE instructor_id=?");
        $stmt->bind_param('i', $this->uid); $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return array_column($rows, 'id');
    }

    // ── Auto-recalculate total marks and pass mark after any question change ──
private function recalcMarks($quiz_id) {
    // Sum all question marks
    $stmt = $this->conn->prepare(
        "SELECT COALESCE(SUM(marks), 0) FROM questions WHERE quiz_id=?"
    );
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    // Pass mark = exactly 50% of total (rounded up)
    $pass_mark = (int) ceil($total * 0.5);

    $stmt = $this->conn->prepare(
        "UPDATE quizzes SET total_marks=?, pass_mark=? WHERE id=?"
    );
    $stmt->bind_param('iii', $total, $pass_mark, $quiz_id);
    $stmt->execute();
    $stmt->close();
}

    public function dashboard() {
        $uid = $this->uid;

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id=? AND status='active'");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($active_courses); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE c.instructor_id=? AND e.status='active'"
        );
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($total_students); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM quizzes WHERE course_id IN (SELECT id FROM courses WHERE instructor_id=?)"
        );
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($total_quizzes); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE c.instructor_id=? AND e.status='pending'"
        );
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($pending_enrollments); $stmt->fetch(); $stmt->close();

        // Recent courses
        $stmt = $this->conn->prepare(
            "SELECT c.*, s.name AS subject,
                    (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled
             FROM courses c JOIN subjects s ON c.subject_id=s.id
             WHERE c.instructor_id=? ORDER BY c.created_at DESC LIMIT 5"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $recent_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT pa.title, pa.body, pa.created_at, u.name AS author
             FROM platform_announcements pa JOIN users u ON pa.author_id=u.id
             ORDER BY pa.created_at DESC LIMIT 5"
        );
        $stmt->execute();
        $platform_announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        require 'views/instructor/dashboard.php';
    }

    public function courses() {
        $uid = $this->uid;
        $stmt = $this->conn->prepare(
            "SELECT c.*, s.name AS subject,
                    (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count,
                    (SELECT COUNT(*) FROM quizzes WHERE course_id=c.id) AS quiz_count,
                    (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='pending') AS pending
             FROM courses c JOIN subjects s ON c.subject_id=s.id
             WHERE c.instructor_id=? ORDER BY c.created_at DESC"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $subjects = $this->conn->query("SELECT * FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        require 'views/instructor/courses.php';
    }

    public function createCourse() {
        $uid = $this->uid;
        $error = $success = '';
        $subjects = $this->conn->query("SELECT * FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title      = trim($_POST['title'] ?? '');
            $subject_id = intval($_POST['subject_id'] ?? 0);
            $desc       = trim($_POST['description'] ?? '');
            $enroll_t   = $_POST['enrollment_type'] ?? 'open';
            $max_s      = intval($_POST['max_students'] ?? 100);
            $status     = $_POST['status'] ?? 'draft';

            if (!$title || !$subject_id) { $error = 'Title and subject are required.'; }
            else {
                $stmt = $this->conn->prepare(
                    "INSERT INTO courses (instructor_id, subject_id, title, description, enrollment_type, max_students, status) VALUES (?,?,?,?,?,?,?)"
                );
                $stmt->bind_param('iisssis', $uid, $subject_id, $title, $desc, $enroll_t, $max_s, $status);
                // Fix types: i i s s s i s
                $stmt->close();
                $stmt = $this->conn->prepare(
                    "INSERT INTO courses (instructor_id, subject_id, title, description, enrollment_type, max_students, status) VALUES (?,?,?,?,?,?,?)"
                );
                $stmt->bind_param('iisssis', $uid, $subject_id, $title, $desc, $enroll_t, $max_s, $status);
                $stmt->close();

                $stmt = $this->conn->prepare(
                    "INSERT INTO courses (instructor_id,subject_id,title,description,enrollment_type,max_students,status) VALUES(?,?,?,?,?,?,?)"
                );
                $stmt->bind_param('iisss' . 'is', $uid, $subject_id, $title, $desc, $enroll_t, $max_s, $status);
                // Actually fix: i,i,s,s,s,i,s => 'iisssis'
                $stmt->close();

                $stmt = $this->conn->prepare(
                    "INSERT INTO courses(instructor_id,subject_id,title,description,enrollment_type,max_students,status)VALUES(?,?,?,?,?,?,?)"
                );
                $stmt->bind_param('iisssis', $uid, $subject_id, $title, $desc, $enroll_t, $max_s, $status);
                if ($stmt->execute()) {
                    $success = 'Course created successfully!';
                } else { $error = 'Failed to create course.'; }
                $stmt->close();
            }
        }
        require 'views/instructor/create_course.php';
    }

    public function editCourse() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
        $error = $success = '';

        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$course) { header('Location: index.php?page=instructor&action=courses'); exit; }

        $subjects = $this->conn->query("SELECT * FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title    = trim($_POST['title'] ?? '');
            $sid      = intval($_POST['subject_id'] ?? 0);
            $desc     = trim($_POST['description'] ?? '');
            $et       = $_POST['enrollment_type'] ?? 'open';
            $max      = intval($_POST['max_students'] ?? 100);
            $status   = $_POST['status'] ?? 'draft';

            if (!$title || !$sid) { $error = 'Title and subject required.'; }
            else {
                $stmt = $this->conn->prepare(
                    "UPDATE courses SET subject_id=?,title=?,description=?,enrollment_type=?,max_students=?,status=? WHERE id=? AND instructor_id=?"
                );
                $stmt->bind_param('isssisii', $sid, $title, $desc, $et, $max, $status, $course_id, $uid);
                if ($stmt->execute()) $success = 'Course updated!';
                else $error = 'Update failed.';
                $stmt->close();
                // Refresh
                $stmt = $this->conn->prepare("SELECT * FROM courses WHERE id=?");
                $stmt->bind_param('i', $course_id); $stmt->execute();
                $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
            }
        }

        // Enrolled students
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.name, u.email, u.student_id, u.program, e.enrolled_at
             FROM enrollments e JOIN users u ON e.student_id=u.id
             WHERE e.course_id=? AND e.status='active' ORDER BY e.enrolled_at"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/instructor/edit_course.php';
    }

    public function enrollments() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$course) { header('Location: index.php?page=instructor&action=courses'); exit; }

        $stmt = $this->conn->prepare(
            "SELECT e.id, e.status, e.enrolled_at, u.name, u.email, u.student_id, u.program
             FROM enrollments e JOIN users u ON e.student_id=u.id
             WHERE e.course_id=? ORDER BY e.status, e.enrolled_at"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/instructor/enrollments.php';
    }

    public function approveEnroll() {
    $uid        = $this->uid;
    $enroll_id  = intval($_POST['enroll_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    $course_id  = intval($_POST['course_id'] ?? 0);
    $redirect   = $_POST['redirect'] ?? 'enrollments';

    if (in_array($new_status, ['active', 'dropped'])) {
        $stmt = $this->conn->prepare(
            "UPDATE enrollments SET status=?
             WHERE id=?
             AND course_id IN (SELECT id FROM courses WHERE instructor_id=?)"
        );
        $stmt->bind_param('sii', $new_status, $enroll_id, $uid);
        $stmt->execute();
        $stmt->close();
    }

    if ($redirect === 'pending_enrollments') {
        header('Location: index.php?page=instructor&action=pending_enrollments');
    } else {
        header('Location: index.php?page=instructor&action=enrollments&course_id=' . $course_id);
    }
    exit;
}

    public function assignTA() {
        $uid       = $this->uid;
        $course_id = intval($_POST['course_id'] ?? 0);
        $ta_id     = intval($_POST['ta_id'] ?? 0);

        // Verify ownership
        $stmt = $this->conn->prepare("SELECT id FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows) {
            $stmt->close();
            // Remove old TA
            $stmt = $this->conn->prepare("DELETE FROM course_tas WHERE course_id=?");
            $stmt->bind_param('i', $course_id); $stmt->execute(); $stmt->close();
            // Assign new
            if ($ta_id) {
                $stmt = $this->conn->prepare("INSERT INTO course_tas (course_id, ta_id) VALUES (?,?)");
                $stmt->bind_param('ii', $course_id, $ta_id); $stmt->execute(); $stmt->close();
            }
        } else { $stmt->close(); }
        header('Location: index.php?page=instructor&action=edit_course&course_id=' . $course_id); exit;
    }

    public function createQuiz() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
        $error     = '';
    
        // Verify instructor owns this course
        $stmt = $this->conn->prepare(
            "SELECT id, title FROM courses WHERE id=? AND instructor_id=?"
        );
        $stmt->bind_param('ii', $course_id, $uid);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    
        if (!$course) {
            header('Location: index.php?page=instructor&action=courses');
            exit;
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quiz'])) {
            $title  = trim($_POST['title'] ?? '');
            $desc   = trim($_POST['description'] ?? '');
            $limit  = intval($_POST['time_limit_minutes'] ?? 30);
            $total  = intval($_POST['total_marks'] ?? 100);
            $pass   = intval($_POST['pass_mark'] ?? 50);
            $type   = $_POST['quiz_type'] ?? 'graded';
            $status = $_POST['status'] ?? 'draft';
            $from   = !empty($_POST['available_from'])  ? $_POST['available_from']  : null;
            $until  = !empty($_POST['available_until']) ? $_POST['available_until'] : null;
    
            if (!$title) {
                $error = 'Quiz title is required.';
            } else {
$stmt = $this->conn->prepare(
    "INSERT INTO quizzes
     (course_id, created_by, title, description,
      time_limit_minutes, total_marks, pass_mark,
      quiz_type, status, available_from, available_until)
     VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?)"
);
$stmt->bind_param(
    'iisisssss',
    $course_id, $uid, $title, $desc,
    $limit,
    $type, $status, $from, $until
);
    
                if ($stmt->execute()) {
                    $new_quiz_id = $this->conn->insert_id;
                    $stmt->close();
                    // ✅ JS redirect — works even if headers already sent
                    echo '<script>window.location.href="index.php?page=instructor&action=manage_quiz&quiz_id=' . $new_quiz_id . '&created=1";</script>';
                    exit;
                } else {
                    $error = 'Failed to create quiz. Please try again.';
                    $stmt->close();
                }
            }
        }
    
        require 'views/instructor/create_quiz.php';
    }

    public function addQuestion() {
        $uid     = $this->uid;
        $quiz_id = intval($_POST['quiz_id'] ?? 0);
    
        // Verify instructor owns this quiz
        $stmt = $this->conn->prepare(
            "SELECT q.id FROM quizzes q
             JOIN courses c ON q.course_id = c.id
             WHERE q.id = ? AND c.instructor_id = ?"
        );
        $stmt->bind_param('ii', $quiz_id, $uid);
        $stmt->execute();
        $stmt->store_result();
        if (!$stmt->num_rows) {
            $stmt->close();
            header('Location: index.php?page=instructor&action=courses');
            exit;
        }
        $stmt->close();
    
        $q_text = trim($_POST['question_text'] ?? '');
        $marks  = max(1, intval($_POST['marks'] ?? 1));
        $options = $_POST['options'] ?? [];
        $correct = intval($_POST['correct_option'] ?? 0);
    
        // Validate
        if (!$q_text) {
            header('Location: index.php?page=instructor&action=manage_quiz&quiz_id=' . $quiz_id . '&err=empty_question');
            exit;
        }
    
        $has_options = false;
        foreach ($options as $opt) {
            if (trim($opt) !== '') { $has_options = true; break; }
        }
        if (!$has_options) {
            header('Location: index.php?page=instructor&action=manage_quiz&quiz_id=' . $quiz_id . '&err=empty_options');
            exit;
        }
    
        // Insert question
        $stmt = $this->conn->prepare(
            "INSERT INTO questions (quiz_id, question_text, marks, created_by) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('isii', $quiz_id, $q_text, $marks, $uid);
        $stmt->execute();
        $q_id = $this->conn->insert_id;
        $stmt->close();
    
        // Insert options
        foreach ($options as $i => $opt_text) {
            $opt_text = trim($opt_text);
            if ($opt_text === '') continue;
            $is_correct = ($i == $correct) ? 1 : 0;
            $stmt = $this->conn->prepare(
                "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)"
            );
            $stmt->bind_param('isi', $q_id, $opt_text, $is_correct);
            $stmt->execute();
            $stmt->close();
        }
    
        $this->recalcMarks($quiz_id);

        // Redirect back to manage quiz
        header('Location: index.php?page=instructor&action=manage_quiz&quiz_id=' . $quiz_id . '&added=1');
        exit;
    }

    public function deleteQuestion() {
        $uid = $this->uid;
        $q_id = intval($_GET['q_id'] ?? 0);
        $quiz_id = intval($_GET['quiz_id'] ?? 0);

        $stmt = $this->conn->prepare(
            "DELETE q FROM questions q JOIN quizzes qz ON q.quiz_id=qz.id JOIN courses c ON qz.course_id=c.id
             WHERE q.id=? AND c.instructor_id=?"
        );
        $stmt->bind_param('ii', $q_id, $uid); $stmt->execute(); $stmt->close();
        $this->recalcMarks($quiz_id); // ← recalculate after delete

        header('Location: index.php?page=instructor&action=manage_quiz&quiz_id=' . $quiz_id); exit;
    }

    public function manageQuiz() {
        $uid     = $this->uid;
        $quiz_id = intval($_GET['quiz_id'] ?? 0);

        $stmt = $this->conn->prepare(
            "SELECT q.*, c.title AS course_title, c.id AS course_id FROM quizzes q JOIN courses c ON q.course_id=c.id WHERE q.id=? AND c.instructor_id=?"
        );
        $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$quiz) { header('Location: index.php?page=instructor&action=courses'); exit; }

        $stmt = $this->conn->prepare("SELECT q.*, (SELECT COUNT(*) FROM options WHERE question_id=q.id) AS option_count FROM questions q WHERE q.quiz_id=? ORDER BY q.order_index, q.id");
        $stmt->bind_param('i', $quiz_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        foreach ($questions as $key => $question) {
            $stmt = $this->conn->prepare("SELECT * FROM options WHERE question_id=?");
            $stmt->bind_param('i', $question['id']); $stmt->execute();
            $questions[$key]['options'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM attempts WHERE quiz_id=?");
        $stmt->bind_param('i', $quiz_id); $stmt->execute(); $stmt->bind_result($attempt_count); $stmt->fetch(); $stmt->close();

        require 'views/instructor/manage_quiz.php';
    }

    public function editQuiz() {
        $uid     = $this->uid;
        $quiz_id = intval($_POST['quiz_id'] ?? 0);

        $stmt = $this->conn->prepare(
            "SELECT q.id FROM quizzes q JOIN courses c ON q.course_id=c.id WHERE q.id=? AND c.instructor_id=?"
        );
        $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); exit; }
        $stmt->close();

        $title  = trim($_POST['title'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $limit  = intval($_POST['time_limit_minutes'] ?? 30);
        $total  = intval($_POST['total_marks'] ?? 100);
        $pass   = intval($_POST['pass_mark'] ?? 50);
        $type   = $_POST['quiz_type'] ?? 'graded';
        $status = $_POST['status'] ?? 'draft';
        $from   = $_POST['available_from'] ?: null;
        $until  = $_POST['available_until'] ?: null;

        $stmt = $this->conn->prepare(
            "UPDATE quizzes SET title=?,description=?,time_limit_minutes=?,total_marks=?,pass_mark=?,quiz_type=?,status=?,available_from=?,available_until=? WHERE id=?"
        );
        $stmt->bind_param('ssiiiisssi', $title, $desc, $limit, $total, $pass, $type, $status, $from, $until, $quiz_id);
        $stmt->execute(); $stmt->close();
        header('Location: index.php?page=instructor&action=manage_quiz&quiz_id=' . $quiz_id); exit;
    }

    public function quizAttempts() {
        $uid     = $this->uid;
        $quiz_id = intval($_GET['quiz_id'] ?? 0);

        $stmt = $this->conn->prepare(
            "SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id=c.id WHERE q.id=? AND c.instructor_id=?"
        );
        $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$quiz) { header('Location: index.php?page=instructor&action=courses'); exit; }

        $stmt = $this->conn->prepare(
            "SELECT a.id, a.score, a.started_at, a.completed_at, u.name, u.student_id,
                    TIMESTAMPDIFF(SECOND, a.started_at, a.completed_at) AS duration_sec
             FROM attempts a JOIN users u ON a.student_id=u.id
             WHERE a.quiz_id=? AND a.completed_at IS NOT NULL
             ORDER BY a.score DESC"
        );
        $stmt->bind_param('i', $quiz_id); $stmt->execute();
        $attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/instructor/quiz_attempts.php';
    }

    public function analytics() {
        $uid = $this->uid;

        $stmt = $this->conn->prepare(
            "SELECT q.id, q.title, q.total_marks, q.pass_mark, c.title AS course_title,
                    COUNT(a.id) AS attempt_count,
                    COALESCE(ROUND(AVG(a.score),1), 0) AS avg_score,
                    COALESCE(MAX(a.score), 0) AS highest,
                    COALESCE(MIN(a.score), 0) AS lowest
             FROM quizzes q
             JOIN courses c ON q.course_id=c.id
             LEFT JOIN attempts a ON a.quiz_id=q.id AND a.completed_at IS NOT NULL
             WHERE c.instructor_id=?
             GROUP BY q.id ORDER BY c.title, q.id"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/instructor/analytics.php';
    }

    public function announcements() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        // Verify ownership
        if ($course_id) {
            $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=? AND instructor_id=?");
            $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
            $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if (!$course) { header('Location: index.php?page=instructor&action=courses'); exit; }
        }

        $stmt = $this->conn->prepare(
            "SELECT a.*, c.title AS course_title FROM announcements a JOIN courses c ON a.course_id=c.id
             WHERE c.instructor_id=? " . ($course_id ? "AND a.course_id=?" : "") . " ORDER BY a.created_at DESC"
        );
        if ($course_id) { $stmt->bind_param('ii', $uid, $course_id); }
        else { $stmt->bind_param('i', $uid); }
        $stmt->execute();
        $announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $courses = $this->conn->query(
            "SELECT id, title FROM courses WHERE instructor_id={$uid} AND status='active' ORDER BY title"
        )->fetch_all(MYSQLI_ASSOC);

        require 'views/instructor/announcements.php';
    }

    public function postAnnouncement() {
        $uid       = $this->uid;
        $course_id = intval($_POST['course_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $body      = trim($_POST['body']  ?? '');

        if ($course_id && $title && $body) {
            $stmt = $this->conn->prepare("SELECT id FROM courses WHERE id=? AND instructor_id=?");
            $stmt->bind_param('ii', $course_id, $uid); $stmt->execute(); $stmt->store_result();
            if ($stmt->num_rows) {
                $stmt->close();
                $stmt = $this->conn->prepare("INSERT INTO announcements(course_id,author_id,title,body) VALUES(?,?,?,?)");
                $stmt->bind_param('iiss', $course_id, $uid, $title, $body); $stmt->execute(); $stmt->close();
            } else { $stmt->close(); }
        }
        header('Location: index.php?page=instructor&action=announcements&course_id=' . $course_id); exit;
    }

    public function materials() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$course) { header('Location: index.php?page=instructor&action=courses'); exit; }

        $stmt = $this->conn->prepare("SELECT * FROM course_materials WHERE course_id=? ORDER BY created_at DESC");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $error = $success = '';
        require 'views/instructor/materials.php';
    }

    public function uploadMaterial() {
        $uid       = $this->uid;
        $course_id = intval($_POST['course_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $type      = $_POST['material_type'] ?? 'document';
        $link      = trim($_POST['link'] ?? '');

        $stmt = $this->conn->prepare("SELECT id FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows || !$title) { $stmt->close(); header('Location: index.php?page=instructor&action=materials&course\_id='.$course_id); exit; }
        $stmt->close();

        $path = $link;
        if ($type !== 'link' && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $ext      = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $filename = 'mat_' . $course_id . '_' . time() . '.' . $ext;
            $dir      = __DIR__ . '/../uploads/materials/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $filename)) $path = $filename;
        }

        $stmt = $this->conn->prepare("INSERT INTO course_materials(course_id,uploaded_by,title,file_path,material_type) VALUES(?,?,?,?,?)");
        $stmt->bind_param('iisss', $course_id, $uid, $title, $path, $type); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=instructor&action=materials&course_id=' . $course_id); exit;
    }

    public function deleteMaterial() {
        $uid = $this->uid;
        $mid = intval($_GET['mat_id'] ?? 0);
        $cid = intval($_GET['course_id'] ?? 0);
        $stmt = $this->conn->prepare(
            "DELETE cm FROM course_materials cm JOIN courses c ON cm.course_id=c.id WHERE cm.id=? AND c.instructor_id=?"
        );
        $stmt->bind_param('ii', $mid, $uid); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=instructor&action=materials&course_id=' . $cid); exit;
    }

    public function qaBoard() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);
    
        // Get all instructor's courses for the picker
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.title,
                    (SELECT COUNT(*) FROM qa_questions WHERE course_id=c.id AND is_resolved=0) AS unresolved
             FROM courses c
             WHERE c.instructor_id=? AND c.status='active'
             ORDER BY c.title"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $my_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        // If no course selected, show course picker
        if (!$course_id) {
            require 'views/instructor/qa_pick_course.php';
            return;
        }
    
        // Verify ownership
        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$course) {
            header('Location: index.php?page=instructor&action=qa_board'); exit;
        }
    
        // Get questions with answers
        $stmt = $this->conn->prepare(
            "SELECT qq.*, u.name AS student_name
             FROM qa_questions qq
             JOIN users u ON qq.student_id = u.id
             WHERE qq.course_id = ?
             ORDER BY qq.is_resolved ASC, qq.created_at DESC"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    
        foreach ($questions as &$q) {
            $stmt = $this->conn->prepare(
                "SELECT qa.*, u.name AS author_name, u.role AS author_role
                 FROM qa_answers qa
                 JOIN users u ON qa.author_id = u.id
                 WHERE qa.qa_question_id = ?
                 ORDER BY qa.is_endorsed DESC, qa.created_at ASC"
            );
            $stmt->bind_param('i', $q['id']); $stmt->execute();
            $q['answers'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        }
    
        require 'views/instructor/qa_board.php';
    }

    public function answerQuestion() {
        $uid  = $this->uid;
        $q_id = intval($_POST['qa_question_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        $cid  = intval($_POST['course_id'] ?? 0);

        if ($q_id && $body) {
            $stmt = $this->conn->prepare("INSERT INTO qa_answers(qa_question_id,author_id,body) VALUES(?,?,?)");
            $stmt->bind_param('iis', $q_id, $uid, $body); $stmt->execute(); $stmt->close();
        }
        header('Location: index.php?page=instructor&action=qa_board&course_id=' . $cid); exit;
    }

    public function endorseAnswer() {
        $uid = $this->uid;
        $aid = intval($_GET['ans_id'] ?? 0);
        $cid = intval($_GET['course_id'] ?? 0);
        $stmt = $this->conn->prepare("UPDATE qa_answers SET is_endorsed=1 WHERE id=?");
        $stmt->bind_param('i', $aid); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=instructor&action=qa_board&course_id=' . $cid); exit;
    }

    public function resolveQuestion() {
        $uid  = $this->uid;
        $q_id = intval($_GET['q_id'] ?? 0);
        $cid  = intval($_GET['course_id'] ?? 0);
        $stmt = $this->conn->prepare("UPDATE qa_questions SET is_resolved=1 WHERE id=?");
        $stmt->bind_param('i', $q_id); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=instructor&action=qa_board&course_id=' . $cid); exit;
    }

    public function courseReport() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE id=? AND instructor_id=?");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$course) { header('Location: index.php?page=instructor&action=courses'); exit; }

        $stmt = $this->conn->prepare(
            "SELECT q.id, q.title, q.total_marks, q.pass_mark,
                    COUNT(a.id) AS attempts,
                    ROUND(AVG(a.score),1) AS avg_score,
                    SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) AS passed
             FROM quizzes q LEFT JOIN attempts a ON a.quiz_id=q.id AND a.completed_at IS NOT NULL
             WHERE q.course_id=? GROUP BY q.id ORDER BY q.id"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $quiz_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        // Enrolled, dropped
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id=? AND status='active'");
        $stmt->bind_param('i', $course_id); $stmt->execute(); $stmt->bind_result($enrolled); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id=? AND status='dropped'");
        $stmt->bind_param('i', $course_id); $stmt->execute(); $stmt->bind_result($dropped); $stmt->fetch(); $stmt->close();

        require 'views/instructor/course_report.php';
    }

    public function pendingEnrollments() {
        $uid = $this->uid;
    
        $stmt = $this->conn->prepare(
            "SELECT e.id, e.enrolled_at,
                    u.name AS student_name, u.email, u.student_id, u.program,
                    c.id AS course_id, c.title AS course_title
             FROM enrollments e
             JOIN users u ON e.student_id = u.id
             JOIN courses c ON e.course_id = c.id
             WHERE c.instructor_id=? AND e.status='pending'
             ORDER BY e.enrolled_at ASC"
        );
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        require 'views/instructor/pending_enrollments.php';
    }


public function profile() {

    $uid = $this->uid;

    $error = '';
    $success = '';

    $stmt = $this->conn->prepare(
        "SELECT * FROM users WHERE id=?"
    );

    $stmt->bind_param('i', $uid);

    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $prog  = trim($_POST['program'] ?? '');

        if (!$name) {

            $error = 'Name cannot be empty.';

        } else {

            $stmt = $this->conn->prepare(
                "UPDATE users
                 SET name=?, phone=?, program=?
                 WHERE id=?"
            );

            $stmt->bind_param(
                'sssi',
                $name,
                $phone,
                $prog,
                $uid
            );

            $stmt->execute();

            $stmt->close();

            $_SESSION['user_name'] = $name;

            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['program'] = $prog;

            $success = 'Profile updated.';
        }

        // PROFILE IMAGE UPLOAD

        if (
            isset($_FILES['profile_pic']) &&
            $_FILES['profile_pic']['error'] === 0
        ) {

            $f = $_FILES['profile_pic'];

            $ext = strtolower(
                pathinfo(
                    $f['name'],
                    PATHINFO_EXTENSION
                )
            );

            if (
                in_array(
                    $ext,
                    ['jpg','jpeg','png','gif','webp']
                )
                &&
                $f['size'] <= 2097152
            ) {

                $fname =
                    'profile_' .
                    $uid .
                    '_' .
                    time() .
                    '.' .
                    $ext;

                $target =
                    'uploads/profiles/' .
                    $fname;

                if (
                    move_uploaded_file(
                        $f['tmp_name'],
                        $target
                    )
                ) {

                    $stmt = $this->conn->prepare(
                        "UPDATE users
                         SET profile_pic=?
                         WHERE id=?"
                    );

                    $stmt->bind_param(
                        'si',
                        $fname,
                        $uid
                    );

                    $stmt->execute();

                    $stmt->close();

                    $_SESSION['profile_pic'] = $fname;

                    $user['profile_pic'] = $fname;

                    $success =
                        'Profile updated with photo.';
                }
            }
        }
    }

    require 'views/instructor/profile.php';
}

public function taQuizApprovals() {
    $uid = $this->uid;

    // Get all draft practice quizzes created by TAs for instructor's courses
    $stmt = $this->conn->prepare(
        "SELECT q.id, q.title, q.total_marks, q.time_limit_minutes,
                c.title AS course_title, c.id AS course_id,
                u.name AS ta_name, u.email AS ta_email,
                (SELECT COUNT(*) FROM questions WHERE quiz_id=q.id) AS question_count
         FROM quizzes q
         JOIN courses c ON q.course_id=c.id
         JOIN users u ON q.created_by=u.id
         WHERE c.instructor_id=?
           AND q.quiz_type='practice'
           AND q.status='draft'
           AND q.created_by != ?
         ORDER BY q.id DESC"
    );
    $stmt->bind_param('ii', $uid, $uid);
    $stmt->execute();
    $pending_quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    require 'views/instructor/ta_quiz_approvals.php';
}

public function approveTaQuiz() {
    $uid     = $this->uid;
    $quiz_id = intval($_POST['quiz_id'] ?? 0);
    $action  = $_POST['approval_action'] ?? '';

    // Verify instructor owns the course this quiz belongs to
    $stmt = $this->conn->prepare(
        "SELECT q.id FROM quizzes q
         JOIN courses c ON q.course_id=c.id
         WHERE q.id=? AND c.instructor_id=?"
    );
    $stmt->bind_param('ii', $quiz_id, $uid);
    $stmt->execute(); $stmt->store_result();
    if (!$stmt->num_rows) { $stmt->close(); header('Location: index.php?page=instructor&action=ta_quiz_approvals'); exit; }
    $stmt->close();

    if ($action === 'approve') {
        $stmt = $this->conn->prepare("UPDATE quizzes SET status='published' WHERE id=?");
        $stmt->bind_param('i', $quiz_id); $stmt->execute(); $stmt->close();
    } elseif ($action === 'reject') {
        $stmt = $this->conn->prepare("DELETE FROM quizzes WHERE id=?");
        $stmt->bind_param('i', $quiz_id); $stmt->execute(); $stmt->close();
    }

    header('Location: index.php?page=instructor&action=ta_quiz_approvals&done=1');
    exit;
}

public function myQuizzes() {
    $uid              = $this->uid;
    $course_id        = intval($_GET['course_id']       ?? 0);
    $quiz_type_filter = $_GET['quiz_type_filter']        ?? '';
    $status_filter    = $_GET['status_filter']           ?? '';
    $date_from        = $_GET['date_from']               ?? '';
    $date_to          = $_GET['date_to']                 ?? '';

    $stmt = $this->conn->prepare(
        "SELECT id, title FROM courses WHERE instructor_id=? ORDER BY title"
    );
    $stmt->bind_param('i', $uid); $stmt->execute();
    $my_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

    $where  = 'c.instructor_id=? AND q.created_by=?';
    $params = [$uid, $uid];
    $types  = 'ii';

    if ($course_id) {
        $where   .= ' AND q.course_id=?';
        $params[] = $course_id;
        $types   .= 'i';
    }
    if ($quiz_type_filter) {
        $where   .= ' AND q.quiz_type=?';
        $params[] = $quiz_type_filter;
        $types   .= 's';
    }
    if ($status_filter) {
        $where   .= ' AND q.status=?';
        $params[] = $status_filter;
        $types   .= 's';
    }
    if ($date_from) {
        $where   .= ' AND DATE(q.id) >= ?';
        $params[] = $date_from;
        $types   .= 's';
    }
    if ($date_to) {
        $where   .= ' AND DATE(q.id) <= ?';
        $params[] = $date_to;
        $types   .= 's';
    }

    $stmt = $this->conn->prepare(
        "SELECT q.id, q.title, q.quiz_type, q.status,
                q.total_marks, q.time_limit_minutes,
                c.title AS course_title, c.id AS course_id,
                (SELECT COUNT(*) FROM questions WHERE quiz_id=q.id) AS question_count,
                (SELECT COUNT(*) FROM attempts  WHERE quiz_id=q.id AND completed_at IS NOT NULL) AS attempt_count
         FROM quizzes q
         JOIN courses c ON q.course_id = c.id
         WHERE {$where}
         ORDER BY q.id DESC"
    );
    if (!$stmt) {
        die("SQL Error: " . $this->conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    require 'views/instructor/my_quizzes.php';
}
}
