<?php
// api/leaderboard.php — AJAX Leaderboard (Member 1 AJAX Feature)
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$quiz_id = intval($_GET['quiz_id'] ?? 0);
if (!$quiz_id) {
    echo json_encode(['success'=>false,'message'=>'Invalid quiz']);
    exit;
}

// Verify quiz exists and get total_marks
$stmt = $conn->prepare(
    "SELECT q.id, q.total_marks, q.course_id FROM quizzes q WHERE q.id=?"
);
$stmt->bind_param('i', $quiz_id); $stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc(); $stmt->close();

if (!$quiz || $quiz['total_marks'] == 0) {
    echo json_encode(['success'=>true, 'data'=>[]]);
    exit;
}

// Get TOP 5 scorers by percentage
$stmt = $conn->prepare(
    "SELECT
        u.name,
        u.student_id,
        MAX(a.score)  AS best_score,
        q.total_marks,
        ROUND((MAX(a.score) * 100.0) / NULLIF(q.total_marks,0), 1) AS score_pct,
        COUNT(a.id) AS attempts
     FROM attempts a
     JOIN users u   ON a.student_id = u.id
     JOIN quizzes q ON a.quiz_id    = q.id
     WHERE a.quiz_id=? AND a.completed_at IS NOT NULL
     GROUP BY a.student_id
     ORDER BY score_pct DESC
     LIMIT 5"
);
$stmt->bind_param('i', $quiz_id); $stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

echo json_encode(['success'=>true, 'data'=>$rows]);