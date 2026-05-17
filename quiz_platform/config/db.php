<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: no password
define('DB_NAME', 'quiz_platform');
define('BASE_URL', 'http://localhost/quiz_platform');
define('UPLOAD_DIR', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_URL', BASE_URL . '/uploads/profiles/');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;color:red">
        <h2>Database Error</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure XAMPP MySQL is running and you have imported schema.sql</p>
    </div>');
}

$conn->set_charset('utf8mb4');

// Add at the bottom of config/db.php
function generateUserId($conn, $user_id, $role) {
    $prefixes = [
        'student'    => 'st',
        'instructor' => 'ins',
        'ta'         => 'ta',
        'admin'      => 'ad',
    ];
    $prefix = $prefixes[$role] ?? 'u';

    // Count only users of THIS role with id <= new user's id
    // This gives the sequential position within the role group
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM users WHERE role = ? AND id <= ?"
    );
    $stmt->bind_param('si', $role, $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $uid_str = $prefix . '-' . $count;

    $stmt = $conn->prepare("UPDATE users SET student_id = ? WHERE id = ?");
    $stmt->bind_param('si', $uid_str, $user_id);
    $stmt->execute();
    $stmt->close();

    return $uid_str;
}