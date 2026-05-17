<?php
// controllers/AuthController.php — Shared login/register/logout

class AuthController {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';

            if (!$email || !$pass) {
                $error = 'Please fill in all fields.';
            } else {
                $stmt = $this->conn->prepare(
                    "SELECT id, name, email, password_hash, role, profile_pic, is_active FROM users WHERE email = ? LIMIT 1"
                );
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$user)                              $error = 'No account found with this email.';
                elseif (!$user['is_active'])             $error = 'DEACTIVATED';
                elseif (!password_verify($pass, $user['password_hash'])) $error = 'Incorrect password.';
                else {
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_name']  = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role']       = $user['role'];
                    $_SESSION['profile_pic']= $user['profile_pic'];
                    header('Location: index.php?page=' . $user['role'] . '&action=dashboard');
                    exit;
                }
            }
        }
        require 'views/auth/login.php';
    }

    public function register() {
        $error = $success = '';
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name    = trim($_POST['name']    ?? '');
            $email   = trim($_POST['email']   ?? '');
            $pass    = $_POST['password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $phone   = trim($_POST['phone']   ?? '');
    
            $errs = [];
            if (!$name)                                      $errs[] = 'Full name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errs[] = 'Valid email required.';
            if (strlen($pass) < 8)                           $errs[] = 'Password must be 8+ characters.';
            if (!preg_match('/[A-Z]/', $pass))               $errs[] = 'Password must contain at least one capital letter.';
            if ($pass !== $confirm)                          $errs[] = 'Passwords do not match.';
    
            if (!$errs) {
                $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=?");
                $stmt->bind_param('s', $email); $stmt->execute(); $stmt->store_result();
                if ($stmt->num_rows) {
                    $error = 'An account with this email already exists.';
                } else {
                    $stmt->close();
                    $hash = password_hash($pass, PASSWORD_BCRYPT);
                    $stmt = $this->conn->prepare(
                        "INSERT INTO users(name,email,password_hash,phone,role,is_active)
                         VALUES(?,?,?,?,'student',1)"
                    );
                    $stmt->bind_param('ssss', $name, $email, $hash, $phone);
                    if ($stmt->execute()) {
                        $new_id = $this->conn->insert_id;
                        $stmt->close();
                        generateUserId($this->conn, $new_id, 'student');
                        $success = 'Account created! Your Student ID is <strong>st-' . $new_id . '</strong>. You can now log in.';
                    } else {
                        $error = 'Registration failed. Please try again.';
                        $stmt->close();
                    }
                }
            } else {
                $error = implode('<br>', $errs);
            }
        }
    
        require 'views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?page=auth&action=login'); exit;
    }
}