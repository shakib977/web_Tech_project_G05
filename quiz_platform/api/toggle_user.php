<?php
// api/toggle_user.php — AJAX endpoint for Admin User Toggle
// MEMBER 4 — AJAX Feature

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false]); exit;
}

$admin_id = $_SESSION['user_id'];
$user\_id  = intval($_POST['user_id'] ?? 0);

if (!$user_id || $user_id === $admin_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid operation']); exit;
}

$stmt = $conn->prepare("SELECT is_active, name FROM users WHERE id=?");
$stmt->bind_param('i', $user_id); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc(); $stmt->close();

if (!$user) { echo json_encode(['success' => false, 'message' => 'User not found']); exit; }

$new_status = $user['is_active'] ? 0 : 1;
$stmt = $conn->prepare("UPDATE users SET is_active=? WHERE id=?");
$stmt->bind_param('ii', $new_status, $user_id); $stmt->execute(); $stmt->close();

// Log action
$action = $new\_status ? "Activated user: {$user['name']} (ID:{$user\_id})" : "Deactivated user: {$user['name']} (ID:{$user_id})";
$stmt = $conn->prepare("INSERT INTO audit_log(admin_id,action) VALUES(?,?)");
$stmt->bind_param('is', $admin_id, $action); $stmt->execute(); $stmt->close();

echo json_encode([
    'success'   => true,
    'is_active' => $new_status,
    'message'   => $new_status ? "User activated." : "User deactivated.",
]);
