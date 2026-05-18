<?php
// api/grade_analytics.php — AJAX endpoint for Instructor Analytics
// MEMBER 2 — AJAX Feature

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false]); exit;
}

$uid     = $_SESSION['user_id'];
$quiz\_id = intval($_GET['quiz_id'] ?? 0);
if (!$quiz_id) { echo json_encode(['success' => false]); exit; }

// Verify ownership
$stmt = $conn->prepare(
    "SELECT q.id FROM quizzes q JOIN courses c ON q.course_id=c.id WHERE q.id=? AND c.instructor_id=?"
);
$stmt->bind_param('ii', $quiz_id, $uid); $stmt->execute(); $stmt->store_result();
if (!$stmt->num_rows) { echo json_encode(['success' => false, 'message' => 'Not your quiz']); exit; }
$stmt->close();

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total, ROUND(AVG(score),1) AS avg_score, MAX(score) AS highest, MIN(score) AS lowest,
            pass_mark,
            SUM(CASE WHEN score >= (SELECT pass_mark FROM quizzes WHERE id=?) THEN 1 ELSE 0 END) AS passed
     FROM attempts WHERE quiz_id=? AND completed_at IS NOT NULL"
);
$stmt->bind_param('ii', $quiz_id, $quiz_id); $stmt->execute();
$d = $stmt->get_result()->fetch_assoc(); $stmt->close();

$pass\_rate = $d\['total'\] > 0 ? round(($d['passed'] / $d['total']) * 100) : 0;

echo json_encode([
    'success' => true,
    'data'    => [
        'total'    => $d['total'] ?? 0,
        'avg_score'=> $d['avg_score'] ?? 0,
        'highest'  => $d['highest'] ?? 0,
        'lowest'   => $d['lowest'] ?? 0,
        'pass_rate'=> $pass_rate,
    ]
]);
