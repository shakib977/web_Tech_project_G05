<?php
// views/layout/header.php — Shared by ALL members
global $conn;
$page   = $_GET['page']   ?? '';
$action = $_GET['action'] ?? 'dashboard';
$role   = $_SESSION['role']       ?? '';
$uid    = $_SESSION['user_id']    ?? 0;
$uname  = $_SESSION['user_name']  ?? 'User';
$uemail = $_SESSION['user_email'] ?? '';
$upic   = $_SESSION['profile_pic']?? 'default.png';
$initial = strtoupper(mb_substr($uname, 0, 1));

// Role-based navigation
$navs = [
    'student' => [
        ['a'=>'dashboard',      'icon'=>'🏠', 'lbl'=>'Dashboard'],
        ['a'=>'browse_courses', 'icon'=>'📚', 'lbl'=>'Browse Courses'],
        ['a'=>'attempt_history','icon'=>'📋', 'lbl'=>'Attempt History'],  // ← ADD
    ['a'=>'qa_courses',     'icon'=>'❓', 'lbl'=>'Q&A Board'],        // ← ADD
        ['a'=>'performance',    'icon'=>'📊', 'lbl'=>'My Performance'],
        ['a'=>'doubt_sessions', 'icon'=>'🎓', 'lbl'=>'Doubt Sessions'],
        ['a'=>'profile',        'icon'=>'👤', 'lbl'=>'My Profile'],
    ],
    'instructor' => [
        ['a'=>'dashboard',    'icon'=>'🏠', 'lbl'=>'Dashboard'],
        ['a'=>'courses',      'icon'=>'📚', 'lbl'=>'My Courses'],
        ['a'=>'my_quizzes',  'icon'=>'📝', 'lbl'=>'Quizzes'],   // ← ADD 
        ['a'=>'analytics',    'icon'=>'📊', 'lbl'=>'Analytics'],
        ['a'=>'qa_board',     'icon'=>'❓', 'lbl'=>'Q&A Board'],
        ['a'=>'profile',      'icon'=>'👤', 'lbl'=>'Profile'],
    ],
    'ta' => [
        ['a'=>'dashboard',      'icon'=>'🏠', 'lbl'=>'Dashboard'],
        ['a'=>'courses',        'icon'=>'📚', 'lbl'=>'My Courses'],
        ['a'=>'doubt_sessions', 'icon'=>'🎓', 'lbl'=>'Doubt Sessions'],
        ['a'=>'at_risk',        'icon'=>'⚠️', 'lbl'=>'At-Risk Students'],
        ['a'=>'qa_board',       'icon'=>'❓', 'lbl'=>'Q&A Board'],
        ['a'=>'profile',        'icon'=>'👤', 'lbl'=>'Profile'],
    ],
    'admin' => [
        ['a'=>'dashboard',    'icon'=>'🏠', 'lbl'=>'Dashboard'],
        ['a'=>'users',        'icon'=>'👥', 'lbl'=>'Users'],
        ['a'=>'courses',      'icon'=>'📚', 'lbl'=>'Courses'],
        ['a'=>'subjects',     'icon'=>'📂', 'lbl'=>'Subjects'],
        ['a'=>'quizzes',      'icon'=>'📝', 'lbl'=>'Quizzes'],
        ['a'=>'analytics',    'icon'=>'📊', 'lbl'=>'Analytics'],
        ['a'=>'integrity',    'icon'=>'🔍', 'lbl'=>'Integrity'],
        ['a'=>'announcements','icon'=>'📣', 'lbl'=>'Announcements'],
        ['a'=>'settings',     'icon'=>'⚙️', 'lbl'=>'Settings'],
        ['a'=>'audit_log',    'icon'=>'📋', 'lbl'=>'Audit Log'],
        ['a'=>'profile',      'icon'=>'👤', 'lbl'=>'Profile'],
    ],
    'instructor' => [
        ['a'=>'dashboard',          'icon'=>'🏠', 'lbl'=>'Dashboard'],
        ['a'=>'courses',            'icon'=>'📚', 'lbl'=>'My Courses'],
        ['a'=>'my_quizzes',         'icon'=>'📝', 'lbl'=>'My Quizzes'],
        ['a'=>'ta_quiz_approvals',  'icon'=>'📨', 'lbl'=>'TA Quiz Approvals'], // ← ADD
        ['a'=>'analytics',          'icon'=>'📊', 'lbl'=>'Analytics'],
        ['a'=>'qa_board',           'icon'=>'❓', 'lbl'=>'Q&A Board'],
        ['a'=>'profile',            'icon'=>'👤', 'lbl'=>'Profile'],
    ],
];

$titles = [
    'dashboard'         => ['Dashboard',       'Welcome back!'],
    'browse_courses'    => ['Browse Courses',   'Discover available courses'],
    'course_detail'     => ['Course Detail',    'Course content and info'],
    'take_quiz'         => ['Take Quiz',        'Good luck! 🍀'],
    'quiz_result'       => ['Quiz Result',      'See how you performed'],
    'attempt_history'   => ['Attempt History',  'All your quiz attempts'],
    'performance'       => ['My Performance',   'Track your progress'],
    'profile'           => ['My Profile',       'Manage your account'],
    'qa_board'          => ['Q&A Board',        'Questions & answers'],
    'doubt_sessions'    => ['Doubt Sessions',   'Book TA sessions'],
    'courses'           => ['My Courses',       'Manage courses'],
    'analytics'         => ['Analytics',        'Performance insights'],
    'users'             => ['User Management',  'Manage platform users'],
    'subjects'          => ['Subjects',         'Manage subject taxonomy'],
    'quizzes'           => ['All Quizzes',      'Platform-wide quizzes'],
    'integrity'         => ['Integrity Flags',  'Review reported content'],
    'announcements'     => ['Announcements',    'Platform announcements'],
    'settings'          => ['Settings',         'Platform configuration'],
    'audit_log'         => ['Audit Log',        'Admin action history'],
    'at_risk'           => ['At-Risk Students', 'Students needing attention'],
    'my_quizzes' => ['My Quizzes', 'Add questions and manage quizzes'],
    'pending_enrollments' => ['Pending Enrollments', 'Approve or reject requests'],
    'ta_quiz_approvals' => ['TA Quiz Approvals', 'Review practice quizzes from TAs'],
    'today_attempts' => ["Today's Attempts", 'Quiz attempts made today'],
    'qa_courses'     => ['Q&A Board',       'Select a course to view Q&A'],
'attempt_history'=> ['Attempt History', 'All your quiz attempts'],

];


$td   = $titles[$action] ?? ['Overview', ''];
$role_labels = ['student'=>'Student','instructor'=>'Instructor','ta'=>'Teaching Assistant','admin'=>'Administrator'];

$pic_src = file_exists('uploads/profiles/' . $upic) ? BASE_URL . '/uploads/profiles/' . htmlspecialchars($upic) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($td[0]) ?> — QuizPro</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app-layout">

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <h2>Quiz<span>Pro</span></h2>
        <span class="sidebar-role-badge badge-<?= $role ?>"><?= $role_labels[$role] ?? $role ?></span>
    </div>

    <nav class="sidebar-nav">
        <?php foreach (($navs[$role] ?? []) as $n): ?>
        <a href="index.php?page=<?= $role ?>&action=<?= $n['a'] ?>"
           class="nav-link <?= $action === $n['a'] ? 'active' : '' ?>">
            <span class="nav-icon"><?= $n['icon'] ?></span>
            <?= htmlspecialchars($n['lbl']) ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <?php if ($pic_src): ?>
                <img src="<?= $pic_src ?>" alt="">
            <?php else: ?>
                <?= $initial ?>
            <?php endif; ?>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($uname) ?></div>
            <div class="sidebar-user-role"><?= $role_labels[$role] ?? '' ?></div>
        </div>
        <a href="javascript:void(0)"
   class="logout-link"
   title="Logout"
   onclick="askLogout('index.php?page=auth&action=logout')">⏏</a>
    </div>
</aside>

<!-- ── MAIN ── -->
<div class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <h1><?= htmlspecialchars($td[0]) ?></h1>
            <?php if ($td[1]): ?>
                <p><?= htmlspecialchars(str_replace('Welcome back!', 'Welcome back, ' . explode(' ',$uname)[0] . '!', $td[1])) ?></p>
            <?php endif; ?>
        </div>
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="topbar-avatar">
                    <?php if ($pic_src): ?>
                        <img src="<?= $pic_src ?>" alt="">
                    <?php else: ?>
                        <?= $initial ?>
                    <?php endif; ?>
                </div>
                <span class="topbar-name"><?= htmlspecialchars(explode(' ',$uname)[0]) ?></span>
            </div>
        </div>
    </header>

    <div class="page-content">