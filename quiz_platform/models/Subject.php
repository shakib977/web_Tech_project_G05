<?php
// models/Subject.php — SHARED (All Members)
class Subject {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getAll() {
        $stmt=$this->conn->prepare("SELECT s.*,(SELECT COUNT(*) FROM courses WHERE subject_id=s.id) AS course_count FROM subjects s ORDER BY s.name");
        $stmt->execute(); $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getById($id) {
        $stmt=$this->conn->prepare("SELECT * FROM subjects WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function create($name,$description) {
        $stmt=$this->conn->prepare("INSERT INTO subjects(name,description) VALUES(?,?)");
        $stmt->bind_param('ss',$name,$description); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function update($id,$name,$description) {
        $stmt=$this->conn->prepare("UPDATE subjects SET name=?,description=? WHERE id=?");
        $stmt->bind_param('ssi',$name,$description,$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function delete($id) {
        $stmt=$this->conn->prepare("DELETE FROM subjects WHERE id=?");
        $stmt->bind_param('i',$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }
}