<?php
// api/flag_student.php — AJAX endpoint for TA At-Risk Flagging
// MEMBER 3 — AJAX Feature

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ta') {
    echo json_encode(['success' => false]); exit;
}

$uid       = $_SESSION['user_id'];
$user\_id   = intval($_POST['user_id'] ?? 0);
$course\_id = intval($_POST['course_id'] ?? 0);

// Verify TA assigned to course
$stmt = $conn->prepare("SELECT id FROM course_tas WHERE ta_id=? AND course_id=?");
$stmt->bind_param('ii', $uid, $course_id); $stmt->execute(); $stmt->store_result();
if (!$stmt->num_rows) { echo json_encode(['success' => false, 'message' => 'Not authorized']); exit; }
$stmt->close();

$reason = 'Flagged by TA as at-risk student (low quiz scores)';
$stmt = $conn->prepare(
    "INSERT INTO integrity_flags(reported_by,course_id,user_id,reason,status) VALUES(?,?,?,?,'pending')"
);
$stmt->bind_param('iiis', $uid, $course_id, $user_id, $reason); $stmt->execute(); $stmt->close();

echo json_encode(['success' => true, 'message' => 'Student flagged for instructor review']);
