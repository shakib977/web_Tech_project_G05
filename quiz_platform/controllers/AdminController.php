<?php
// controllers/AdminController.php
// ██████████████████████████████████████████
// MEMBER 4 — ADMIN
// ██████████████████████████████████████████

class AdminController {
    private $conn;
    private $uid;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->uid  = $_SESSION['user_id'];
    }

    private function logAction($action) {
        $uid = $this->uid;
        $stmt = $this->conn->prepare("INSERT INTO audit_log(admin_id,action) VALUES(?,?)");
        $stmt->bind_param('is', $uid, $action); $stmt->execute(); $stmt->close();
    }

    public function dashboard() {
        // Counts by role
        $stmt = $this->conn->prepare("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role");
        $stmt->execute(); $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $role_counts = [];
        foreach ($rows as $r) $role_counts[$r['role']] = $r['cnt'];

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM courses WHERE status='active'");
        $stmt->execute(); $stmt->bind_result($active_courses); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM attempts WHERE DATE(started_at)=CURDATE()");
        $stmt->execute(); $stmt->bind_result($attempts_today); $stmt->fetch(); $stmt->close();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM integrity_flags WHERE status='pending'");
        $stmt->execute(); $stmt->bind_result($pending_flags); $stmt->fetch(); $stmt->close();

        // Recent registrations
        $stmt = $this->conn->prepare("SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC LIMIT 8");
        $stmt->execute();
        $recent_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/admin/dashboard.php';
    }

    public function users() {
        $role   = $_GET['role']   ?? '';
        $search = trim($_GET['search'] ?? '');

        $where  = ['1=1']; $params = []; $types = '';
        if ($role)   { $where[] = 'role = ?';                          $params[] = $role;             $types .= 's'; }
        if ($search) { $like = '%'.$search.'%'; $wher[] = '(name LIKE ? OR email LIKE ? OR student_id LIKE ?)'; $params=array_merge($params,[$like,$like,$like]); $types.='sss'; }

        $sql  = "SELECT id,name,email,phone,role,student_id,program,is_active,created_at FROM users WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/admin/users.php';
    }

    

    public function changeRole() {
        $uid     = $this->uid;
        $user_id = intval($_POST['user_id'] ?? 0);
        $role    = $_POST['role'] ?? '';

        if (in_array($role, ['student','instructor','ta','admin']) && $user_id !== $uid) {
            $stmt = $this->conn->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param('si', $role, $user_id); $stmt->execute(); $stmt->close();
            $this->logAction("Changed role of user ID {$user_id} to {$role}");
        }
        header('Location: index.php?page=admin&action=users'); exit;
    }

    public function approveInstructor() {
        $user_id = intval($_POST['user_id'] ?? 0);
        $action  = $_POST['approval'] ?? '';

        if ($action === 'approve') {
            $stmt = $this->conn->prepare("UPDATE users SET role='instructor', is_active=1 WHERE id=?");
            $stmt->bind_param('i', $user_id); $stmt->execute(); $stmt->close();
            $this->logAction("Approved instructor account ID {$user_id}");
        } elseif ($action === 'reject') {
            $stmt = $this->conn->prepare("UPDATE users SET is_active=0 WHERE id=?");
            $stmt->bind_param('i', $user_id); $stmt->execute(); $stmt->close();
            $this->logAction("Rejected instructor account ID {$user_id}");
        }
        header('Location: index.php?page=admin&action=users&role=instructor'); exit;
    }

    // File: controllers/AdminController.php

public function createTA() {

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (!$name || !$email || !$pass) {

            $error = 'All fields required.';

        } else {

            $hash = password_hash($pass, PASSWORD_BCRYPT);

            $stmt = $this->conn->prepare(
                "INSERT INTO users
                (name, email, password_hash, role, is_active)
                VALUES (?, ?, ?, 'ta', 1)"
            );

            if (!$stmt) {
                die($this->conn->error);
            }

            $stmt->bind_param(
                'sss',
                $name,
                $email,
                $hash
            );

            if ($stmt->execute()) {

                $success = "TA account created successfully.";

            } else {

                $error = "Failed to create TA.";

            }

            $stmt->close();
        }
    }

    require 'views/admin/create_ta.php';
}

    public function courses() {
        $subject_id = intval($_GET['subject_id'] ?? 0);
        $status     = $_GET['status'] ?? '';

        $where = ['1=1']; $params = []; $types = '';
        if ($subject_id) { $where[] = 'c.subject_id=?'; $params[]=$subject_id; $types.='i'; }
        if ($status)     { $where[] = 'c.status=?';     $params[]=$status;     $types.='s'; }

        $sql  = "SELECT c.*, s.name AS subject, u.name AS instructor,
                        (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count,
                        (SELECT COUNT(*) FROM quizzes WHERE course_id=c.id) AS quiz_count
                 FROM courses c JOIN subjects s ON c.subject_id=s.id JOIN users u ON c.instructor_id=u.id
                 WHERE " . implode(' AND ', $where) . " ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        $subjects = $this->conn->query("SELECT * FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        require 'views/admin/courses.php';
    }

    public function subjects() {
        $error = $success = '';
        $subjects = $this->conn->query("SELECT s.*, (SELECT COUNT(*) FROM courses WHERE subject_id=s.id) AS course_count FROM subjects s ORDER BY s.name")->fetch_all(MYSQLI_ASSOC);
        require 'views/admin/subjects.php';
    }

    public function addSubject() {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name) {
            $stmt = $this->conn->prepare("INSERT INTO subjects(name,description) VALUES(?,?)");
            $stmt->bind_param('ss', $name, $desc); $stmt->execute(); $stmt->close();
            $this->logAction("Added subject: {$name}");
        }
        header('Location: index.php?page=admin&action=subjects'); exit;
    }

    public function editSubject() {
        $id   = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($id && $name) {
            $stmt = $this->conn->prepare("UPDATE subjects SET name=?,description=? WHERE id=?");
            $stmt->bind_param('ssi', $name, $desc, $id); $stmt->execute(); $stmt->close();
        }
        header('Location: index.php?page=admin&action=subjects'); exit;
    }

    public function deleteSubject() {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $this->conn->prepare("DELETE FROM subjects WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
        $this->logAction("Deleted subject ID {$id}");
        header('Location: index.php?page=admin&action=subjects'); exit;
    }

    public function quizzes() {
        $course_id = intval($_GET['course_id'] ?? 0);
        $type      = $_GET['type'] ?? '';
        $status    = $_GET['status'] ?? '';

        $where = ['1=1']; $params = []; $types_str = '';
        if ($course_id) { $where[]='q.course_id=?'; $params[]=$course_id; $types_str.='i'; }
        if ($type)      { $where[]='q.quiz_type=?';  $params[]=$type;      $types_str.='s'; }
        if ($status)    { $where[]='q.status=?';     $params[]=$status;    $types_str.='s'; }

        $sql  = "SELECT q.*, c.title AS course_title, u.name AS creator,
                        (SELECT COUNT(*) FROM attempts WHERE quiz_id=q.id) AS attempt_count
                 FROM quizzes q JOIN courses c ON q.course_id=c.id JOIN users u ON q.created_by=u.id
                 WHERE " . implode(' AND ', $where) . " ORDER BY q.id DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types_str, ...$params);
        $stmt->execute();
        $quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/admin/quizzes.php';
    }

    public function integrity() {
        $stmt = $this->conn->prepare(
            "SELECT f.*, u.name AS reported_user, c.title AS course_title, r.name AS reporter_name
             FROM integrity_flags f
             LEFT JOIN users u ON f.user_id=u.id
             LEFT JOIN courses c ON f.course_id=c.id
             LEFT JOIN users r ON f.reported_by=r.id
             ORDER BY f.status='pending' DESC, f.created_at DESC"
        );
        $stmt->execute();
        $flags = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        require 'views/admin/integrity.php';
    }

    public function resolveFlag() {
        $flag_id = intval($_POST['flag_id'] ?? 0);
        $status  = $_POST['flag_status'] ?? 'resolved';
        if (in_array($status, ['resolved','escalated'])) {
            $stmt = $this->conn->prepare("UPDATE integrity_flags SET status=? WHERE id=?");
            $stmt->bind_param('si', $status, $flag_id); $stmt->execute(); $stmt->close();
            $this->logAction("Updated integrity flag ID {$flag_id} to {$status}");
        }
        header('Location: index.php?page=admin&action=integrity'); exit;
    }

    public function analytics() {
        // Enrollments per subject
        $stmt = $this->conn->prepare(
            "SELECT s.name, COUNT(e.id) AS enrollments FROM enrollments e JOIN courses c ON e.course_id=c.id JOIN subjects s ON c.subject_id=s.id WHERE e.status='active' GROUP BY s.id ORDER BY enrollments DESC"
        );
        $stmt->execute();
        $by_subject = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        // Pass rates per subject
        $stmt = $this->conn->prepare(
            "SELECT s.name,
                    COUNT(a.id) AS attempts,
                    SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) AS passed
             FROM attempts a JOIN quizzes q ON a.quiz_id=q.id JOIN courses c ON q.course_id=c.id JOIN subjects s ON c.subject_id=s.id
             WHERE a.completed_at IS NOT NULL
             GROUP BY s.id ORDER BY s.name"
        );
        $stmt->execute();
        $pass_by_subject = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        // Top instructors
        $stmt = $this->conn->prepare(
            "SELECT u.name, COUNT(DISTINCT c.id) AS courses, COUNT(DISTINCT e.id) AS students
             FROM users u LEFT JOIN courses c ON c.instructor_id=u.id AND c.status='active'
             LEFT JOIN enrollments e ON e.course_id=c.id AND e.status='active'
             WHERE u.role='instructor' GROUP BY u.id ORDER BY students DESC LIMIT 10"
        );
        $stmt->execute();
        $top_instructors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/admin/analytics.php';
    }

    public function announcements() {
        $stmt = $this->conn->prepare("SELECT pa.*, u.name AS author FROM platform_announcements pa JOIN users u ON pa.author_id=u.id ORDER BY pa.created_at DESC");
        $stmt->execute();
        $announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $error = $success = '';
        require 'views/admin/announcements.php';
    }

    public function postAnnouncement() {
        $uid   = $this->uid;
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        if ($title && $body) {
            $stmt = $this->conn->prepare("INSERT INTO platform_announcements(author_id,title,body) VALUES(?,?,?)");
            $stmt->bind_param('iss', $uid, $title, $body); $stmt->execute(); $stmt->close();
            $this->logAction("Posted platform announcement: {$title}");
        }
        header('Location: index.php?page=admin&action=announcements'); exit;
    }

    public function studentReport() {
        $search = trim($_GET['search'] ?? '');
        $users  = [];
        if ($search) {
            $like = '%' . $search . '%';
            $stmt = $this->conn->prepare(
                "SELECT id,name,email,student_id,program FROM users WHERE role='student' AND (name LIKE ? OR email LIKE ? OR student_id LIKE ?) LIMIT 20"
            );
            $stmt->bind_param('sss', $like, $like, $like); $stmt->execute();
            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        }

        $selected_user = null;
        $user_stats    = [];
        $uid_sel       = intval($_GET['uid'] ?? 0);
        if ($uid_sel) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=? AND role='student'");
            $stmt->bind_param('i', $uid_sel); $stmt->execute();
            $selected_user = $stmt->get_result()->fetch_assoc(); $stmt->close();

            if ($selected_user) {
                $stmt = $this->conn->prepare(
                    "SELECT c.title AS course_title, s.name AS subject,
                            COUNT(a.id) AS attempts, ROUND(AVG(a.score),1) AS avg_score
                     FROM enrollments e
                     JOIN courses c ON e.course_id=c.id
                     JOIN subjects s ON c.subject_id=s.id
                     LEFT JOIN quizzes q ON q.course_id=c.id
                     LEFT JOIN attempts a ON a.quiz_id=q.id AND a.student_id=? AND a.completed_at IS NOT NULL
                     WHERE e.student_id=? AND e.status='active'
                     GROUP BY c.id ORDER BY c.title"
                );
                $stmt->bind_param('ii', $uid_sel, $uid_sel); $stmt->execute();
                $user_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
            }
        }

        require 'views/admin/student_report.php';
    }

    public function institutionalReport() {
        $from = $_GET['from'] ?? date('Y-01-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $stmt = $this->conn->prepare(
            "SELECT s.name AS subject,
                    COUNT(DISTINCT c.id) AS courses,
                    COUNT(DISTINCT e.id) AS enrollments,
                    COUNT(DISTINCT a.id) AS attempts,
                    ROUND(AVG(a.score),1) AS avg_score,
                    SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) AS passed
             FROM subjects s
             LEFT JOIN courses c ON c.subject_id=s.id
             LEFT JOIN enrollments e ON e.course_id=c.id AND e.status='active'
             LEFT JOIN quizzes q ON q.course_id=c.id
             LEFT JOIN attempts a ON a.quiz_id=q.id AND a.completed_at IS NOT NULL AND DATE(a.completed_at) BETWEEN ? AND ?
             GROUP BY s.id ORDER BY enrollments DESC"
        );
        $stmt->bind_param('ss', $from, $to); $stmt->execute();
        $report = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

        require 'views/admin/institutional_report.php';
    }

    public function settings() {
        $stmt = $this->conn->prepare("SELECT * FROM platform_settings ORDER BY setting_key");
        $stmt->execute();
        $settings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    
        $s_map = [];
        foreach ($settings as $s) $s_map[$s['setting_key']] = $s['setting_value'];
    
        $success = '';
        require 'views/admin/settings.php';
    }

    public function saveSettings() {
        $uid = $this->uid;
    
        // Handle logo upload separately
        if (isset($_FILES['platform_logo']) && $_FILES['platform_logo']['error'] === 0) {
            $f    = $_FILES['platform_logo'];
            $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','svg'];
    
            if (in_array($ext, $allowed) && $f['size'] <= 2 * 1024 * 1024) {
                $dir   = __DIR__ . '/../uploads/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $fname = 'platform_logo.' . $ext;
    
                if (move_uploaded_file($f['tmp_name'], $dir . $fname)) {
                    // Save logo path to settings
                    $stmt = $this->conn->prepare(
                        "INSERT INTO platform_settings(setting_key,setting_value)
                         VALUES('logo_path',?)
                         ON DUPLICATE KEY UPDATE setting_value=?"
                    );
                    $stmt->bind_param('ss', $fname, $fname);
                    $stmt->execute(); $stmt->close();
                }
            }
        }
    
        // Save text settings — use UPDATE (settings already exist from seed)
        $keys = [
            'max_quiz_duration',
            'max_students_per_course',
            'platform_name',
            'at_risk_threshold',
        ];
    
        foreach ($keys as $key) {
            if (!isset($_POST[$key])) continue;
            $val = trim($_POST[$key]);
    
            // Try UPDATE first, then INSERT if not exists
            $stmt = $this->conn->prepare(
                "INSERT INTO platform_settings(setting_key, setting_value)
                 VALUES(?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            $stmt->bind_param('ss', $key, $val);
            $stmt->execute();
            $stmt->close();
        }
    
        $this->logAction('Updated platform settings');
        header('Location: index.php?page=admin&action=settings&saved=1');
        exit;
    }

    public function auditLog() {
        $stmt = $this->conn->prepare(
            "SELECT al.*, u.name AS admin_name FROM audit_log al JOIN users u ON al.admin_id=u.id ORDER BY al.created_at DESC LIMIT 200"
        );
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        require 'views/admin/audit_log.php';
    }

    public function profile() {
        $uid   = $this->uid;
        $error = $success = '';
    
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc(); $stmt->close();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form_type = $_POST['form_type'] ?? 'info';
    
            if ($form_type === 'info') {
                $name  = trim($_POST['name']  ?? '');
                $phone = trim($_POST['phone'] ?? '');
                if (!$name) {
                    $error = 'Name cannot be empty.';
                } else {
                    $stmt = $this->conn->prepare(
                        "UPDATE users SET name=?, phone=? WHERE id=?"
                    );
                    $stmt->bind_param('ssi', $name, $phone, $uid);
                    $stmt->execute(); $stmt->close();
                    $_SESSION['user_name'] = $name;
                    $user['name']  = $name;
                    $user['phone'] = $phone;
                    $success = 'Profile updated successfully.';
                }
    
            } elseif ($form_type === 'photo') {
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
                    $f    = $_FILES['profile_pic'];
                    $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png','gif','webp'];
    
                    if (!in_array($ext, $allowed)) {
                        $error = 'Only JPG, PNG, GIF or WebP images allowed.';
                    } elseif ($f['size'] > 2 * 1024 * 1024) {
                        $error = 'Image must be under 2MB.';
                    } else {
                        $dir  = __DIR__ . '/../uploads/profiles/';
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
    
                        $fname = 'profile_' . $uid . '_' . time() . '.' . $ext;
    
                        if (move_uploaded_file($f['tmp_name'], $dir . $fname)) {
                            $stmt = $this->conn->prepare(
                                "UPDATE users SET profile_pic=? WHERE id=?"
                            );
                            $stmt->bind_param('si', $fname, $uid);
                            $stmt->execute(); $stmt->close();
                            $_SESSION['profile_pic'] = $fname;
                            $user['profile_pic'] = $fname;
                            $success = 'Profile photo updated.';
                        } else {
                            $error = 'Failed to save image. Check uploads/profiles/ folder permissions.';
                        }
                    }
                } else {
                    $error = 'No file selected or upload error.';
                }
            }
        }
    
        require 'views/admin/profile.php';
    }

    // ── ADMIN: Manage a specific course ──────────────
public function courseManage() {
    $course_id = intval($_GET['course_id'] ?? 0);
    $error = $success = '';

    // Get course info
    $stmt = $this->conn->prepare(
        "SELECT c.*, s.name AS subject_name, u.name AS instructor_name
         FROM courses c
         JOIN subjects s ON c.subject_id = s.id
         JOIN users   u ON c.instructor_id = u.id
         WHERE c.id = ?"
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$course) {
        header('Location: index.php?page=admin&action=courses');
        exit;
    }

    // Get current assigned TA
    $stmt = $this->conn->prepare(
        "SELECT u.id, u.name, u.email
         FROM course_tas ct
         JOIN users u ON ct.ta_id = u.id
         WHERE ct.course_id = ?
         LIMIT 1"
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $current_ta = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Get all TA accounts
    $stmt = $this->conn->prepare(
        "SELECT id, name, email FROM users
         WHERE role = 'ta' AND is_active = 1
         ORDER BY name"
    );
    $stmt->execute();
    $all_tas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get enrolled students count
    $stmt = $this->conn->prepare(
        "SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status = 'active'"
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $stmt->bind_result($enrolled_count);
    $stmt->fetch();
    $stmt->close();

    // Get quizzes
    $stmt = $this->conn->prepare(
        "SELECT q.id, q.title, q.quiz_type, q.status, q.total_marks,
                (SELECT COUNT(*) FROM attempts WHERE quiz_id = q.id AND completed_at IS NOT NULL) AS attempt_count
         FROM quizzes q
         WHERE q.course_id = ?
         ORDER BY q.id DESC"
    );
    if (!$stmt) {
        die($this->conn->error);
    }
    $stmt->bind_param('i', $course_id);
    
    $stmt->execute();
    $quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Handle POST — assign TA
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_ta'])) {
        $ta_id = intval($_POST['ta_id'] ?? 0);

        // Remove existing TA assignment
        $stmt = $this->conn->prepare("DELETE FROM course_tas WHERE course_id = ?");
        $stmt->bind_param('i', $course_id);
        $stmt->execute();
        $stmt->close();

        if ($ta_id) {
            $stmt = $this->conn->prepare(
                "INSERT INTO course_tas (course_id, ta_id) VALUES (?, ?)"
            );
            $stmt->bind_param('ii', $course_id, $ta_id);
            $stmt->execute();
            $stmt->close();
            $this->logAction("Assigned TA ID {$ta_id} to course ID {$course_id}");
            $success = 'Teaching Assistant assigned successfully.';

            // Refresh current TA
            $stmt = $this->conn->prepare(
                "SELECT u.id, u.name, u.email FROM course_tas ct
                 JOIN users u ON ct.ta_id = u.id WHERE ct.course_id = ? LIMIT 1"
            );
            $stmt->bind_param('i', $course_id);
            $stmt->execute();
            $current_ta = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $this->logAction("Removed TA from course ID {$course_id}");
            $success = 'TA removed from this course.';
            $current_ta = null;
        }
    }

    require 'views/admin/course_manage.php';
}

public function todayAttempts() {
    $stmt = $this->conn->prepare(
        "SELECT a.id, a.score, a.started_at, a.completed_at,
                u.name AS student_name, u.student_id,
                q.title AS quiz_title, q.total_marks, q.pass_mark,
                c.title AS course_title
         FROM attempts a
         JOIN users   u ON a.student_id = u.id
         JOIN quizzes q ON a.quiz_id    = q.id
         JOIN courses c ON q.course_id  = c.id
         WHERE DATE(a.started_at) = CURDATE()
         ORDER BY a.started_at DESC"
    );
    $stmt->execute();
    $attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total    = count($attempts);
    $completed = count(array_filter($attempts, fn($a) => $a['completed_at']));
    $passed    = count(array_filter($attempts, fn($a) => $a['completed_at'] && $a['score'] >= $a['pass_mark']));

    require 'views/admin/today_attempts.php';
}
// ── ADMIN: View enrolled students in a course ─────
public function courseStudents() {
    $course_id = intval($_GET['course_id'] ?? 0);

    $stmt = $this->conn->prepare(
        "SELECT c.title, c.id FROM courses c WHERE c.id = ?"
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$course) {
        header('Location: index.php?page=admin&action=courses');
        exit;
    }

    // Get enrolled students with their quiz performance
    $stmt = $this->conn->prepare(
        "SELECT u.id, u.name, u.email, u.student_id, u.program,
                e.status AS enrollment_status, e.enrolled_at,
                (SELECT COUNT(*) FROM attempts a
                 JOIN quizzes q ON a.quiz_id = q.id
                 WHERE a.student_id = u.id AND q.course_id = ? AND a.completed_at IS NOT NULL
                ) AS attempt_count,
                (SELECT ROUND(AVG(a.score), 1) FROM attempts a
                 JOIN quizzes q ON a.quiz_id = q.id
                 WHERE a.student_id = u.id AND q.course_id = ? AND a.completed_at IS NOT NULL
                ) AS avg_score
         FROM enrollments e
         JOIN users u ON e.student_id = u.id
         WHERE e.course_id = ?
         ORDER BY e.status, u.name"
    );
    $stmt->bind_param('iii', $course_id, $course_id, $course_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Counts
    $active  = count(array_filter($students, fn($s) => $s['enrollment_status'] === 'active'));
    $pending = count(array_filter($students, fn($s) => $s['enrollment_status'] === 'pending'));
    $dropped = count(array_filter($students, fn($s) => $s['enrollment_status'] === 'dropped'));

    require 'views/admin/course_students.php';
}
public function createInstructor() {
    $error = $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $pass    = $_POST['password']     ?? '';
        $phone   = trim($_POST['phone']   ?? '');
        $program = trim($_POST['program'] ?? '');

        if (!$name || !$email || !$pass) {
            $error = 'Name, email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            // Check email unique
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param('s', $email); $stmt->execute(); $stmt->store_result();
            if ($stmt->num_rows) {
                $error = 'An account with this email already exists.';
            } else {
                $stmt->close();
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = $this->conn->prepare(
                    "INSERT INTO users(name,email,password_hash,phone,role,program,is_active)
                     VALUES(?,?,?,?,'instructor',?,1)"
                );
                $stmt->bind_param('sssss', $name, $email, $hash, $phone, $program);
                if ($stmt->execute()) {
                    $new_id = $this->conn->insert_id;
                    $stmt->close();
                    generateUserId($this->conn, $new_id, 'instructor');
                    $this->logAction("Created instructor account: {$email} (ins-{$new_id})");
                    $success = "Instructor account created! ID: <strong>ins-{$new_id}</strong>. They can log in immediately.";
                } else {
                    $error = 'Failed to create account. Please try again.';
                    $stmt->close();
                }
            }
        }
    }

    require 'views/admin/create_instructor.php';
}

// Fix deactivation — no AJAX, direct POST
public function toggleUser() {
    $admin_id = (int) $_SESSION['user_id'];
    $user_id  = (int) ($_GET['user_id'] ?? 0);

    if (!$user_id) {
        header('Location: index.php?page=admin&action=users');
        exit;
    }

    // Cannot deactivate yourself
    if ($user_id === $admin_id) {
        header('Location: index.php?page=admin&action=users&err=self');
        exit;
    }

    // Fetch user
    $stmt = $this->conn->prepare(
        "SELECT id, name, role, is_active FROM users WHERE id = ?"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        header('Location: index.php?page=admin&action=users&err=notfound');
        exit;
    }

    // Cannot deactivate admin accounts
    if ($user['role'] === 'admin') {
        header('Location: index.php?page=admin&action=users&err=admin');
        exit;
    }

    $new_status = $user['is_active'] ? 0 : 1;

    $stmt = $this->conn->prepare(
        "UPDATE users SET is_active = ? WHERE id = ?"
    );
    $stmt->bind_param('ii', $new_status, $user_id);
    $stmt->execute();
    $stmt->close();

    $word = $new_status ? 'Activated' : 'Deactivated';
    $this->logAction("{$word} user: {$user['name']} (ID:{$user_id})");

    header('Location: index.php?page=admin&action=users&msg=' . urlencode($word . ' successfully'));
    exit;
}
}
