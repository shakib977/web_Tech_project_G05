<?php
// api/enroll.php — AJAX Enrollment Endpoint
// MEMBER 1 — AJAX Feature

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$uid       = (int) $_SESSION['user_id'];
$course_id = (int) ($_POST['course_id'] ?? 0);

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course.']);
    exit;
}

// Get course info
$stmt = $conn->prepare(
    "SELECT id, title, enrollment_type, status, max_students
     FROM courses WHERE id = ? AND status = 'active'"
);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not active.']);
    exit;
}

// Check if already enrolled (any status)
$stmt = $conn->prepare(
    "SELECT id, status FROM enrollments WHERE student_id = ? AND course_id = ?"
);
$stmt->bind_param('ii', $uid, $course_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    if ($existing['status'] === 'active') {
        echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course.']);
        exit;
    }
    if ($existing['status'] === 'pending') {
        echo json_encode(['success' => false, 'message' => 'Your enrollment request is already pending.']);
        exit;
    }
    // Status is 'dropped' — allow re-enrollment by updating
    $new_status = ($course['enrollment_type'] === 'open') ? 'active' : 'pending';
    $stmt = $conn->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $existing['id']);
    $stmt->execute();
    $stmt->close();

    $msg = ($new_status === 'active')
        ? 'Re-enrolled successfully!'
        : 'Re-enrollment request submitted. Awaiting approval.';
    echo json_encode(['success' => true, 'message' => $msg, 'status' => $new_status]);
    exit;
}

// Check capacity
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status = 'active'"
);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$stmt->bind_result($enrolled_count);
$stmt->fetch();
$stmt->close();

if ($enrolled_count >= $course['max_students']) {
    echo json_encode(['success' => false, 'message' => 'This course is full.']);
    exit;
}

// Fresh enrollment
$status = ($course['enrollment_type'] === 'open') ? 'active' : 'pending';
$stmt = $conn->prepare(
    "INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, ?)"
);
$stmt->bind_param('iis', $uid, $course_id, $status);

if ($stmt->execute()) {
    $stmt->close();
    $msg = ($status === 'active')
        ? 'Enrolled successfully! 🎉'
        : 'Enrollment request sent. Awaiting instructor approval.';
    echo json_encode(['success' => true, 'message' => $msg, 'status' => $status]);
} else {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Enrollment failed. Please try again.']);
}