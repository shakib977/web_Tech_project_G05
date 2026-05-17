<?php
// api/leaderboard.php — AJAX endpoint for Student Leaderboard
// MEMBER 1 — AJAX Feature

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$quiz\_id = intval($_GET['quiz_id'] ?? 0);
if (!$quiz_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit;
}

// Verify student is enrolled in the course this quiz belongs to
$stmt = $conn->prepare(
    "SELECT q.course_id FROM quizzes q WHERE q.id = ?"
);
$stmt->bind_param('i', $quiz_id); $stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc(); $stmt->close();

if (!$quiz) {
    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
    exit;
}

$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=? AND status='active'");
$stmt->bind_param('ii', $uid, $quiz['course_id']); $stmt->execute(); $stmt->store_result();
if (!$stmt->num_rows) {
    echo json_encode(['success' => false, 'message' => 'Not enrolled']);
    exit;
}
$stmt->close();

// Get top 10 scores for this quiz
$stmt = $conn->prepare(
    "SELECT u.name, u.program, MAX(a.score) AS score
     FROM attempts a
     JOIN users u ON a.student_id = u.id
     WHERE a.quiz_id = ? AND a.completed_at IS NOT NULL
     GROUP BY a.student_id
     ORDER BY score DESC
     LIMIT 10"
);
$stmt->bind_param('i', $quiz_id); $stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

echo json_encode(['success' => true, 'data' => $rows]);
