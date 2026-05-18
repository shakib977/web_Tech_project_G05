<?php
// controllers/TAController.php
// ██████████████████████████████████████████
// MEMBER 3 — TEACHING ASSISTANT
// ██████████████████████████████████████████

class TAController {
    private $conn;
    private $uid;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->uid  = $_SESSION['user_id'];
    }

    private function myCoursesIds() {
        $stmt = $this->conn->prepare("SELECT course_id FROM course_tas WHERE ta_id=?");
        $stmt->bind_param('i', $this->uid); $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return array_column($rows, 'course_id');
    }

    public function dashboard() {
        $uid = $this->uid;

        $courses = $this->myCourses();
        $course_count = count($courses);

        // Total students in TA's courses
        $ids = $this->myCoursesIds();
        $total_students = 0;
        if ($ids) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT student_id) FROM enrollments WHERE course_id IN ($ph) AND status='active'");
            $stmt->bind_param($types, ...$ids); $stmt->execute(); $stmt->bind_result($total_students); $stmt->fetch(); $stmt->close();
        }

        // Get at-risk threshold
        $stmt = $this->conn->prepare("SELECT setting_value FROM platform_settings WHERE setting_key='at_risk_threshold'");
        $stmt->execute(); $stmt->bind_result($threshold); $stmt->fetch(); $stmt->close();
        $threshold = intval($threshold ?? 50);

        // At-risk count
        $at_risk_count = 0;
        if ($ids) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $stmt = $this->conn->prepare(
                "SELECT COUNT(DISTINCT a.student_id) FROM attempts a JOIN quizzes q ON a.quiz_id=q.id
                 WHERE q.course_id IN ($ph) AND a.completed_at IS NOT NULL AND a.score < ?"
            );
            $params = array_merge($ids, [$threshold]);
            $stmt->bind_param($types . 'i', ...$params); $stmt->execute(); $stmt->bind_result($at_risk_count); $stmt->fetch(); $stmt->close();
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM doubt_sessions WHERE ta_id=? AND scheduled_at > NOW() AND is_cancelled=0");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->bind_result($upcoming_sessions); $stmt->fetch(); $stmt->close();

        // 
$stmt = $this->conn->prepare(
    "SELECT pa.title, pa.body, pa.created_at, u.name AS author
     FROM platform_announcements pa JOIN users u ON pa.author_id=u.id
     ORDER BY pa.created_at DESC LIMIT 5"
);
$stmt->execute();
$platform_announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
        require 'views/ta/dashboard.php';
    }

    private function myCourses() {
        $uid = $this->uid;
        $stmt = $this->conn->prepare(
            "SELECT c.*, s.name AS subject, u.name AS instructor_name,
                    (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled
             FROM course_tas ct
             JOIN courses c ON ct.course_id=c.id
             JOIN subjects s ON c.subject_id=s.id
             JOIN users u ON c.instructor_id=u.id
             WHERE ct.ta_id=? ORDER BY c.title"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $courses;
    }

    public function courses() {
        $courses = $this->myCourses();
        require 'views/ta/courses.php';
    }

    public function requestApproval() {
        $uid     = $this->uid;
        $quiz_id = intval($_POST['quiz_id'] ?? 0);
    
        // Verify TA owns this quiz
        $stmt = $this->conn->prepare("SELECT id, course_id FROM quizzes WHERE id=? AND created_by=?");
        $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc(); $stmt->close();
    
        if (!$quiz) {
            header('Location: index.php?page=ta&action=courses'); exit;
        }
    
        // Mark as pending approval (use a special status)
        $stmt = $this->conn->prepare(
            "UPDATE quizzes SET status='draft' WHERE id=? AND created_by=?"
        );
        $stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute(); $stmt->close();
    
        header('Location: index.php?page=ta&action=manage_quiz&quiz_id='.$quiz_id.'&requested=1');
        exit;
    }

    public function courseDetail() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { header('Location: index.php?page=ta&action=courses'); exit; }
        $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT c.*,
        s.name AS subject,
        u.name AS instructor,

        (SELECT COUNT(*)
         FROM enrollments
         WHERE course_id = c.id
         AND status='active') AS enrolled_count

 FROM courses c
 JOIN subjects s ON c.subject_id=s.id
 JOIN users u ON c.instructor_id=u.id

 WHERE c.id=?"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT u.id, u.name, u.email, u.student_id FROM enrollments e JOIN users u ON e.student_id=u.id WHERE e.course_id=? AND e.status='active' ORDER BY u.name"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM quizzes WHERE course_id=? ORDER BY status, id DESC");
        if (!$stmt) {
            die($this->conn->error);
        }
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/ta/course_detail.php';
    }

    public function createQuiz() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
        $error     = '';
    
        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); header('Location: index.php?page=ta&action=courses'); exit; }
        $stmt->close();
    
        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quiz'])) {
            $title = trim($_POST['title'] ?? '');
            $desc  = trim($_POST['description'] ?? '');
            $limit = intval($_POST['time_limit_minutes'] ?? 30);
            $from  = !empty($_POST['available_from'])  ? $_POST['available_from']  : null;
            $until = !empty($_POST['available_until']) ? $_POST['available_until'] : null;
    
            if (!$title) {
                $error = 'Quiz title is required.';
            } else {
                
    
                $stmt = $this->conn->prepare(
                    "INSERT INTO quizzes
                     (course_id,created_by,title,description,
                      time_limit_minutes,total_marks,pass_mark,
                      quiz_type,status,available_from,available_until)
                     VALUES (?,?,?,?,?,0,0,'practice','draft',?,?)"
                );
                $stmt->bind_param('iississ', $course_id, $uid, $title, $desc, $limit, $from, $until);
    
                if ($stmt->execute()) {
                    $new_quiz_id = $this->conn->insert_id;
                    $stmt->close();
                    echo '<script>window.location.href="index.php?page=ta&action=manage_quiz&quiz_id='.$new_quiz_id.'&created=1";</script>';
                    exit;
                } else {
                    $error = 'Failed to create quiz.';
                    $stmt->close();
                }
            }
        }
    
        require 'views/ta/create_quiz.php';
    }

    public function questionBank() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); header('Location: index.php?page=ta&action=courses'); exit; }
        $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT q.*, qz.title AS quiz_title FROM questions q JOIN quizzes qz ON q.quiz_id=qz.id WHERE qz.course_id=? AND q.created_by=? ORDER BY qz.title, q.id"
        );
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        require 'views/ta/question_bank.php';
    }

    public function manageQuiz() {
        $uid     = $this->uid;
        $quiz_id = intval($_GET['quiz_id'] ?? 0);
    
        // Verify TA created this quiz
        $stmt = $this->conn->prepare(
            "SELECT q.*, c.title AS course_title, c.id AS course_id,
                    ct.id AS ta_assignment
             FROM quizzes q
             JOIN courses c ON q.course_id=c.id
             JOIN course_tas ct ON ct.course_id=c.id AND ct.ta_id=?
             WHERE q.id=? AND q.created_by=?"
        );
        $stmt->bind_param('iii', $uid, $quiz_id, $uid);
        $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    
        if (!$quiz) {
            header('Location: index.php?page=ta&action=courses'); exit;
        }
    
        // Get questions
        $stmt = $this->conn->prepare(
            "SELECT * FROM questions WHERE quiz_id=? ORDER BY order_index, id"
        );
        $stmt->bind_param('i', $quiz_id);
        $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        foreach ($questions as $key => $q) {
            $stmt = $this->conn->prepare("SELECT * FROM options WHERE question_id=?");
            $stmt->bind_param('i', $q['id']); $stmt->execute();
            $questions[$key]['options'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    
        require 'views/ta/manage_quiz.php';
    }

    public function addQuestion() {
        $uid     = $this->uid;
        $quiz_id = intval($_POST['quiz_id'] ?? 0);
        $cid     = intval($_POST['course_id'] ?? 0);
    
        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $cid); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); exit; }
        $stmt->close();
    
        $q_text  = trim($_POST['question_text'] ?? '');
        $marks   = max(1, intval($_POST['marks'] ?? 1));
        $options = $_POST['options'] ?? [];
        $correct = intval($_POST['correct_option'] ?? 0);
    
        if ($q_text) {
            $stmt = $this->conn->prepare(
                "INSERT INTO questions(quiz_id,question_text,marks,created_by) VALUES(?,?,?,?)"
            );
            $stmt->bind_param('isii', $quiz_id, $q_text, $marks, $uid);
            $stmt->execute();
            $q_id = $this->conn->insert_id;
            $stmt->close();
    
            foreach ($options as $i => $opt) {
                $opt = trim($opt);
                if (!$opt) continue;
                $is_c = ($i == $correct) ? 1 : 0;
                $stmt = $this->conn->prepare(
                    "INSERT INTO options(question_id,option_text,is_correct) VALUES(?,?,?)"
                );
                $stmt->bind_param('isi', $q_id, $opt, $is_c);
                $stmt->execute(); $stmt->close();
            }
    
            // Recalc marks
            $this->recalcMarks($quiz_id);
        }
    
        header('Location: index.php?page=ta&action=manage_quiz&quiz_id='.$quiz_id.'&added=1');
        exit;
    }

    public function deleteQuestion() {
        $uid     = $this->uid;
        $q_id    = intval($_GET['q_id'] ?? 0);
        $quiz_id = intval($_GET['quiz_id'] ?? 0);
    
        $stmt = $this->conn->prepare(
            "DELETE FROM questions WHERE id=? AND created_by=?"
        );
        $stmt->bind_param('ii', $q_id, $uid);
        $stmt->execute(); $stmt->close();
    
        $this->recalcMarks($quiz_id);
        header('Location: index.php?page=ta&action=manage_quiz&quiz_id='.$quiz_id.'&deleted=1');
        exit;
    }

    private function recalcMarks($quiz_id) {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(marks),0) FROM questions WHERE quiz_id=?"
        );
        $stmt->bind_param('i', $quiz_id);
        $stmt->execute(); $stmt->bind_result($total); $stmt->fetch(); $stmt->close();
    
        $stmt = $this->conn->prepare(
            "UPDATE quizzes SET total_marks=?, pass_mark=0 WHERE id=?"
        );  // practice quiz: no pass_mark
        $stmt->bind_param('ii', $total, $quiz_id);
        $stmt->execute(); $stmt->close();
    }

    public function studentResults() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); header('Location: index.php?page=ta&action=courses'); exit; }
        $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT a.id, a.score, a.started_at, a.completed_at, u.name AS student_name, u.student_id,
                    q.title AS quiz_title, q.total_marks, q.pass_mark,
                    TIMESTAMPDIFF(SECOND, a.started_at, a.completed_at) AS duration
             FROM attempts a JOIN users u ON a.student_id=u.id JOIN quizzes q ON a.quiz_id=q.id
             WHERE q.course_id=? AND a.completed_at IS NOT NULL ORDER BY a.completed_at DESC"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        require 'views/ta/student_results.php';
    }

    public function atRisk() {
        $uid  = $this->uid;
        $ids  = $this->myCoursesIds();

        $stmt = $this->conn->prepare("SELECT setting_value FROM platform_settings WHERE setting_key='at_risk_threshold'");
        $stmt->execute(); $stmt->bind_result($threshold); $stmt->fetch(); $stmt->close();
        $threshold = intval($threshold ?? 50);

        $at_risk = [];
        if ($ids) {
            $ph    = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $stmt  = $this->conn->prepare(
                "SELECT DISTINCT u.id, u.name, u.email, u.student_id, c.id AS course_id, c.title AS course_title,
                        ROUND(AVG(a.score),1) AS avg_score, COUNT(a.id) AS attempt_count
                 FROM attempts a
                 JOIN users u ON a.student_id=u.id
                 JOIN quizzes q ON a.quiz_id=q.id
                 JOIN courses c ON q.course_id=c.id
                 WHERE q.course_id IN ($ph) AND a.completed_at IS NOT NULL
                 GROUP BY u.id, c.id
                 HAVING avg_score < ?
                 ORDER BY avg_score ASC"
            );
            $params = array_merge($ids, [$threshold]);
            $stmt->bind_param($types . 'i', ...$params); $stmt->execute();
            $at_risk = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        }

        require 'views/ta/at_risk.php';
    }

   

    public function materials() {
        $uid = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); header('Location: index.php?page=ta&action=courses'); exit; }
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM course_materials WHERE course_id=? AND uploaded_by=? ORDER BY created_at DESC");
        $stmt->bind_param('ii', $course_id, $uid); $stmt->execute();
        $materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        $error = $success = '';
        require 'views/ta/materials.php';
    }

    public function uploadMaterial() {
        $uid       = $this->uid;
        $course_id = intval($_POST['course_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $type      = $_POST['material_type'] ?? 'document';
        $link      = trim($_POST['link'] ?? '');

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows || !$title) { $stmt->close(); header('Location: index.php?page=ta&action=materials&course\_id='.$course_id); exit; }
        $stmt->close();

        $path = $link;
        if ($type !== 'link' && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $ext  = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $fname= 'mat_ta_' . $uid . '_' . time() . '.' . $ext;
            $dir  = __DIR__ . '/../uploads/materials/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $fname)) $path = $fname;
        }

        $stmt = $this->conn->prepare("INSERT INTO course_materials(course_id,uploaded_by,title,file_path,material_type)VALUES(?,?,?,?,?)");
        $stmt->bind_param('iisss', $course_id, $uid, $title, $path, $type); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=ta&action=materials&course_id=' . $course_id); exit;
    }

    public function deleteMaterial() {
        $uid = $this->uid;
        $mid = intval($_GET['mat_id'] ?? 0);
        $cid = intval($_GET['course_id'] ?? 0);
        $stmt = $this->conn->prepare("DELETE FROM course_materials WHERE id=? AND uploaded_by=?");
        $stmt->bind_param('ii', $mid, $uid); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=ta&action=materials&course_id=' . $cid); exit;
    }

    public function qaBoard() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);
    
        // Get assigned courses for picker
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.title,
                    (SELECT COUNT(*) FROM qa_questions WHERE course_id=c.id AND is_resolved=0) AS unresolved
             FROM course_tas ct
             JOIN courses c ON ct.course_id = c.id
             WHERE ct.ta_id = ? ORDER BY c.title"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $my_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        // No course selected — show picker
        if (!$course_id) {
            require 'views/ta/qa_pick_course.php';
            return;
        }
    
        // Verify assignment
        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) {
            $stmt->close();
            header('Location: index.php?page=ta&action=qa_board'); exit;
        }
        $stmt->close();
    
        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();
    
        $stmt = $this->conn->prepare(
            "SELECT qq.*, u.name AS student_name FROM qa_questions qq
             JOIN users u ON qq.student_id = u.id
             WHERE qq.course_id = ?
             ORDER BY qq.is_resolved ASC, qq.created_at DESC"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    
        foreach ($questions as &$q) {
            $stmt = $this->conn->prepare(
                "SELECT qa.*, u.name AS author_name, u.role FROM qa_answers qa
                 JOIN users u ON qa.author_id = u.id
                 WHERE qa.qa_question_id = ?
                 ORDER BY qa.is_endorsed DESC, qa.created_at ASC"
            );
            $stmt->bind_param('i', $q['id']); $stmt->execute();
            $q['answers'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        }
    
        require 'views/ta/qa_board.php';
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
        header('Location: index.php?page=ta&action=qa_board&course_id=' . $cid); exit;
    }

    public function endorseAnswer() {
        $uid = $this->uid;
        $aid = intval($_GET['ans_id'] ?? 0);
        $cid = intval($_GET['course_id'] ?? 0);
        $stmt = $this->conn->prepare("UPDATE qa_answers SET is_endorsed=1 WHERE id=?");
        $stmt->bind_param('i', $aid); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=ta&action=qa_board&course_id=' . $cid); exit;
    }

    public function doubtSessions() {
        $uid = $this->uid;

        $stmt = $this->conn->prepare(
            "SELECT ds.*, c.title AS course_title,
                    (SELECT COUNT(*) FROM doubt_session_bookings WHERE doubt_session_id=ds.id) AS bookings
             FROM doubt_sessions ds JOIN courses c ON ds.course_id=c.id
             WHERE ds.ta_id=? ORDER BY ds.scheduled_at DESC"
        );
        $stmt->bind_param('i', $uid); $stmt->execute();
        $sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $courses = $this->myCourses();
        $error = $success = '';
        require 'views/ta/doubt_sessions.php';
    }

    public function createSession() {
        $uid       = $this->uid;
        $course_id = intval($_POST['course_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $sched     = $_POST['scheduled_at'] ?? '';
        $dur       = intval($_POST['duration_minutes'] ?? 60);
        $loc       = trim($_POST['location_or_link'] ?? '');
        $max_att   = intval($_POST['max_attendees'] ?? 20);

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows && $title && $sched) {
            $stmt->close();
            $stmt = $this->conn->prepare(
                "INSERT INTO doubt_sessions(course_id,ta_id,title,scheduled_at,duration_minutes,location_or_link,max_attendees) VALUES(?,?,?,?,?,?,?)"
            );
            $stmt->bind_param('iissisi', $course_id, $uid, $title, $sched, $dur, $loc, $max_att);
            $stmt->execute(); $stmt->close();
        } else { $stmt->close(); }
        header('Location: index.php?page=ta&action=doubt_sessions'); exit;
    }

    public function cancelSession() {
        $uid        = $this->uid;
        $session_id = intval($_POST['session_id'] ?? 0);
        $reason     = trim($_POST['reason'] ?? '');

        $stmt = $this->conn->prepare("UPDATE doubt_sessions SET is_cancelled=1, cancel_reason=? WHERE id=? AND ta_id=?");
        $stmt->bind_param('sii', $reason, $session_id, $uid); $stmt->execute(); $stmt->close();
        header('Location: index.php?page=ta&action=doubt_sessions'); exit;
    }

    public function sessionBookings() {
        $uid        = $this->uid;
        $session_id = intval($_GET['session_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT * FROM doubt_sessions WHERE id=? AND ta_id=?");
        $stmt->bind_param('ii', $session_id, $uid); $stmt->execute();
        $session = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$session) { header('Location: index.php?page=ta&action=doubt_sessions'); exit; }

        $stmt = $this->conn->prepare(
            "SELECT u.name, u.email, u.student_id, b.booked_at FROM doubt_session_bookings b JOIN users u ON b.student_id=u.id WHERE b.doubt_session_id=? ORDER BY b.booked_at"
        );
        $stmt->bind_param('i', $session_id); $stmt->execute();
        $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/ta/session_bookings.php';
    }

    public function courseSummary() {
        $uid       = $this->uid;
        $course_id = intval($_GET['course_id'] ?? 0);

        $stmt = $this->conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
        $stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) { $stmt->close(); header('Location: index.php?page=ta&action=courses'); exit; }
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT setting_value FROM platform_settings WHERE setting_key='at_risk_threshold'");
        $stmt->execute(); $stmt->bind_result($thr); $stmt->fetch(); $stmt->close();
        $threshold = intval($thr ?? 50);

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id=? AND status='active'");
        $stmt->bind_param('i', $course_id); $stmt->execute(); $stmt->bind_result($total_students); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT a.student_id) FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE q.course_id=? AND a.completed_at IS NOT NULL"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute(); $stmt->bind_result($attempted); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT ROUND(AVG(a.score),1) FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE q.course_id=? AND a.completed_at IS NOT NULL"
        );
        $stmt->bind_param('i', $course_id); $stmt->execute(); $stmt->bind_result($avg_score); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT a.student_id) FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE q.course_id=? AND a.completed_at IS NOT NULL AND a.score < ?"
        );
        $stmt->bind_param('ii', $course_id, $threshold); $stmt->execute(); $stmt->bind_result($at_risk_count); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT id, title FROM courses WHERE id=?");
        $stmt->bind_param('i', $course_id); $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc(); $stmt->close();

        require 'views/ta/course_summary.php';
    }

    public function profile() {
        $uid = $this->uid;
        $error = $success = '';

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc(); $stmt->close();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name  = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if ($name) {
                $stmt = $this->conn->prepare("UPDATE users SET name=?,phone=? WHERE id=?");
                $stmt->bind_param('ssi', $name, $phone, $uid); $stmt->execute(); $stmt->close();
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated.'; $user['name'] = $name;
            } else { $error = 'Name is required.'; }
        }

        require 'views/ta/profile.php';
    }

    public function flagStudent() {
        $uid       = $this->uid;
        $user_id   = intval($_POST['user_id']   ?? 0);
        $course_id = intval($_POST['course_id'] ?? 0);
    
        // Verify TA is assigned to this course
        $stmt = $this->conn->prepare(
            "SELECT id FROM course_tas WHERE ta_id=? AND course_id=?"
        );
        $stmt->bind_param('ii', $uid, $course_id);
        $stmt->execute(); $stmt->store_result();
        if (!$stmt->num_rows) {
            $stmt->close();
            header('Location: index.php?page=ta&action=at_risk&err=unauthorized');
            exit;
        }
        $stmt->close();
    
        // Check if already flagged (avoid duplicates)
        $stmt = $this->conn->prepare(
            "SELECT id FROM integrity_flags
             WHERE reported_by=? AND user_id=? AND course_id=? AND status='pending'"
        );
        $stmt->bind_param('iii', $uid, $user_id, $course_id);
        $stmt->execute(); $stmt->store_result();
        $already = $stmt->num_rows > 0;
        $stmt->close();
    
        if (!$already) {
            $reason = 'Student flagged as at-risk by TA (low quiz scores)';
            $stmt = $this->conn->prepare(
                "INSERT INTO integrity_flags(reported_by,course_id,user_id,reason,status)
                 VALUES(?,?,?,?,'pending')"
            );
            $stmt->bind_param('iiis', $uid, $course_id, $user_id, $reason);
            $stmt->execute(); $stmt->close();
        }
    
        header('Location: index.php?page=ta&action=at_risk&msg=flagged');
        exit;
    }
}
