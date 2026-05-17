<?php
// models/User.php — SHARED (All Members)
class User {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function findById($id) {
        $stmt = $this->conn->prepare(
            "SELECT id,name,email,phone,role,profile_pic,student_id,program,is_active,created_at FROM users WHERE id=?"
        );
        $stmt->bind_param('i', $id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function create($name, $email, $password, $role, $phone='', $student_id='', $program='') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO users(name,email,password_hash,phone,role,student_id,program,is_active) VALUES(?,?,?,?,?,?,?,1)"
        );
        $stmt->bind_param('ssssss s', $name, $email, $hash, $phone, $role, $student_id, $program);
        $stmt->close();
        // Fixed:
        $stmt = $this->conn->prepare(
            "INSERT INTO users(name,email,password_hash,phone,role,student_id,program,is_active) VALUES(?,?,?,?,?,?,?,1)"
        );
        $stmt->bind_param('sssssss', $name, $email, $hash, $phone, $role, $student_id, $program);
        $ok = $stmt->execute(); $id = $this->conn->insert_id; $stmt->close();
        return $ok ? $id : false;
    }

    public function update($id, $name, $phone, $program) {
        $stmt = $this->conn->prepare("UPDATE users SET name=?,phone=?,program=? WHERE id=?");
        $stmt->bind_param('sssi', $name, $phone, $program, $id);
        $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function updatePassword($id, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $stmt->bind_param('si', $hash, $id); $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function updateProfilePic($id, $filename) {
        $stmt = $this->conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
        $stmt->bind_param('si', $filename, $id); $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function setActive($id, $active) {
        $stmt = $this->conn->prepare("UPDATE users SET is_active=? WHERE id=?");
        $stmt->bind_param('ii', $active, $id); $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function setRole($id, $role) {
        $stmt = $this->conn->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->bind_param('si', $role, $id); $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function getAll($role = null, $search = '') {
        $where = ['1=1']; $params = []; $types = '';
        if ($role)   { $where[] = 'role=?';                                           $params[] = $role;                  $types .= 's'; }
        if ($search) { $like = '%'.$search.'%'; $where[] = '(name LIKE ? OR email LIKE ? OR student_id LIKE ?)'; $params = array_merge($params, [$like,$like,$like]); $types .= 'sss'; }
        $sql  = "SELECT id,name,email,phone,role,student_id,program,is_active,created_at FROM users WHERE ".implode(' AND ',$where)." ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute(); $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getTAs() {
        $stmt = $this->conn->prepare("SELECT id,name,email FROM users WHERE role='ta' AND is_active=1 ORDER BY name");
        $stmt->execute(); $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function countByRole() {
        $stmt = $this->conn->prepare("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role");
        $stmt->execute(); $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $out = [];
        foreach ($rows as $row) $out[$row['role']] = $row['cnt'];
        return $out;
    }
}