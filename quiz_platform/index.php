<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// index.php — Front Controller Router
ob_start(); // ← ADD THIS — buffers output so header() redirects always work
session_start();
require_once __DIR__ . '/config/db.php';

$page   = $_GET['page']   ?? 'auth';
$action = $_GET['action'] ?? 'login';

// ... rest of your switch statement stays exactly the same

switch ($page) {

    // ── AUTH (login/register/logout) ── shared
    case 'auth':
        require_once 'controllers/AuthController.php';
        $ctrl = new AuthController($conn);
        if ($action === 'register') $ctrl->register();
        elseif ($action === 'logout') $ctrl->logout();
        else $ctrl->login();
        break;

    // ── MEMBER 1: STUDENT ──────────────────────────
    case 'student':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
            header('Location: index.php?page=auth&action=login'); exit;
        }
        require_once 'controllers/StudentController.php';
        $ctrl = new StudentController($conn);
        switch ($action) {
            case 'dashboard':       $ctrl->dashboard();      break;
            case 'browse_courses':  $ctrl->browseCourses();  break;
            case 'course_detail':   $ctrl->courseDetail();   break;
            case 'take_quiz':       $ctrl->takeQuiz();       break;
            case 'submit_quiz':     $ctrl->submitQuiz();     break;
            case 'quiz_result':     $ctrl->quizResult();     break;
            case 'attempt_history': $ctrl->attemptHistory(); break;
            case 'performance':     $ctrl->performance();    break;
            case 'profile':         $ctrl->profile();        break;
            case 'qa_board':        $ctrl->qaBoard();        break;
            case 'post_question':   $ctrl->postQuestion();   break;
            case 'mark_resolved':   $ctrl->markResolved();   break;
            case 'doubt_sessions':  $ctrl->doubtSessions();  break;
            case 'book_session':    $ctrl->bookSession();    break;
            case 'drop_course':     $ctrl->dropCourse();     break;
            case 'qa_courses': $ctrl->qaCourses(); break;
            default:                $ctrl->dashboard();
        }
        break;

    // ── MEMBER 2: INSTRUCTOR ───────────────────────
    case 'instructor':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
            header('Location: index.php?page=auth&action=login'); exit;
        }
        require_once 'controllers/InstructorController.php';
        $ctrl = new InstructorController($conn);
        switch ($action) {
            case 'dashboard':          $ctrl->dashboard();         break;
            case 'courses':            $ctrl->courses();           break;
            case 'create_course':      $ctrl->createCourse();      break;
            case 'edit_course':        $ctrl->editCourse();        break;
            case 'enrollments':        $ctrl->enrollments();       break;
            case 'approve_enroll':     $ctrl->approveEnroll();     break;
            case 'assign_ta':          $ctrl->assignTA();          break;
            case 'create_quiz':        $ctrl->createQuiz();        break;
            case 'edit_quiz':          $ctrl->editQuiz();          break;
            case 'add_question':       $ctrl->addQuestion();       break;
            case 'delete_question':    $ctrl->deleteQuestion();    break;
            case 'quiz_attempts':      $ctrl->quizAttempts();      break;
            case 'analytics':          $ctrl->analytics();         break;
            case 'announcements':      $ctrl->announcements();     break;
            case 'post_announcement':  $ctrl->postAnnouncement();  break;
            case 'materials':          $ctrl->materials();         break;
            case 'upload_material':    $ctrl->uploadMaterial();    break;
            case 'delete_material':    $ctrl->deleteMaterial();    break;
            case 'qa_board':           $ctrl->qaBoard();           break;
            case 'answer_question':    $ctrl->answerQuestion();    break;
            case 'endorse_answer':     $ctrl->endorseAnswer();     break;
            case 'resolve_question':   $ctrl->resolveQuestion();   break;
            case 'course_report':      $ctrl->courseReport();      break;
            case 'profile':            $ctrl->profile();           break;
            case 'my_quizzes': $ctrl->myQuizzes(); break;  // 
            case 'manage_quiz':        $ctrl->manageQuiz();        break;  // 
            //case 'my_quizzes':         $ctrl->myQuizzes();         break; 
            case 'pending_enrollments': $ctrl->pendingEnrollments(); break; // 
            case 'ta_quiz_approvals': $ctrl->taQuizApprovals(); break;  // ← ADD
case 'approve_ta_quiz':   $ctrl->approveTaQuiz();   break;  // ← ADD
            default:                   $ctrl->dashboard();
        }
        break;

    // ── MEMBER 3: TEACHING ASSISTANT ──────────────
    case 'ta':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ta') {
            header('Location: index.php?page=auth&action=login'); exit;
        }
        require_once 'controllers/TAController.php';
        $ctrl = new TAController($conn);
        switch ($action) {
            case 'dashboard':        $ctrl->dashboard();       break;
            case 'courses':          $ctrl->courses();         break;
            case 'course_detail':    $ctrl->courseDetail();    break;
            case 'create_quiz':      $ctrl->createQuiz();      break;
            case 'question_bank':    $ctrl->questionBank();    break;
            case 'add_question':     $ctrl->addQuestion();     break;
            case 'edit_question':    $ctrl->editQuestion();    break;
            case 'delete_question':  $ctrl->deleteQuestion();  break;
            case 'student_results':  $ctrl->studentResults();  break;
            case 'at_risk':          $ctrl->atRisk();          break;
            case 'flag_student':     $ctrl->flagStudent();     break;
            case 'post_announcement':$ctrl->postAnnouncement();break;
            case 'materials':        $ctrl->materials();       break;
            case 'upload_material':  $ctrl->uploadMaterial();  break;
            case 'delete_material':  $ctrl->deleteMaterial();  break;
            case 'qa_board':         $ctrl->qaBoard();         break;
            case 'answer_question':  $ctrl->answerQuestion();  break;
            case 'endorse_answer':   $ctrl->endorseAnswer();   break;
            case 'doubt_sessions':   $ctrl->doubtSessions();   break;
            case 'create_session':   $ctrl->createSession();   break;
            case 'cancel_session':   $ctrl->cancelSession();   break;
            case 'session_bookings': $ctrl->sessionBookings(); break;
            case 'course_summary':   $ctrl->courseSummary();   break;
            case 'profile':          $ctrl->profile();         break;
            case 'flag_student': $ctrl->flagStudent(); break;
            case 'manage_quiz':   $ctrl->manageQuiz();   break;  // 
case 'add_question':  $ctrl->addQuestion();  break;  // 
case 'delete_question': $ctrl->deleteQuestion(); break; // 
case 'request_approval': $ctrl->requestApproval(); break;
            default:                 $ctrl->dashboard();
        }
        break;

    // ── MEMBER 4: ADMIN ────────────────────────────
    case 'admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?page=auth&action=login'); exit;
        }
        require_once 'controllers/AdminController.php';
        $ctrl = new AdminController($conn);
        switch ($action) {
            case 'dashboard':            $ctrl->dashboard();           break;
            case 'users':                $ctrl->users();               break;
            case 'toggle_user':          $ctrl->toggleUser();          break;
            case 'change_role':          $ctrl->changeRole();          break;
            case 'approve_instructor':   $ctrl->approveInstructor();   break;
            case 'create_ta':            $ctrl->createTA();            break;
            case 'courses':              $ctrl->courses();             break;
            case 'subjects':             $ctrl->subjects();            break;
            case 'add_subject':          $ctrl->addSubject();          break;
            case 'edit_subject':         $ctrl->editSubject();         break;
            case 'delete_subject':       $ctrl->deleteSubject();       break;
            case 'quizzes':              $ctrl->quizzes();             break;
            case 'integrity':            $ctrl->integrity();           break;
            case 'resolve_flag':         $ctrl->resolveFlag();         break;
            case 'analytics':            $ctrl->analytics();           break;
            case 'announcements':        $ctrl->announcements();       break;
            case 'post_announcement':    $ctrl->postAnnouncement();    break;
            case 'student_report':       $ctrl->studentReport();       break;
            case 'institutional_report': $ctrl->institutionalReport(); break;
            case 'settings':             $ctrl->settings();            break;
            case 'save_settings':        $ctrl->saveSettings();        break;
            case 'audit_log':            $ctrl->auditLog();            break;
            case 'profile':              $ctrl->profile();             break;
            case 'course_manage':   $ctrl->courseManage();   break; 
            case 'course_students': $ctrl->courseStudents();  break;  
            case 'today_attempts': $ctrl->todayAttempts(); break;
            case 'create_instructor': $ctrl->createInstructor(); break;
            case 'toggle_user':       $ctrl->toggleUser();       break;
            default:                     $ctrl->dashboard();
        }
        break;

    default:
        header('Location: index.php?page=auth&action=login'); exit;
}