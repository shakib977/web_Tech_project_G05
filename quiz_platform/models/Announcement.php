<?php
// models/Announcement.php — SHARED (All Members)
class Announcement {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getByCourse($course_id) {
        $stmt = $this->conn->prepare(
            "SELECT a.*,u.name AS author FROM announcements a JOIN users u ON a.author_id=u.id WHERE a.course_id=? ORDER BY a.created_at DESC"
        );
        $stmt->bind_param('i',$course_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function create($course_id,$author_id,$title,$body,$from_ta=0) {
        $stmt = $this->conn->prepare("INSERT INTO announcements(course_id,author_id,title,body,from_ta) VALUES(?,?,?,?,?)");
        $stmt->bind_param('iissi',$course_id,$author_id,$title,$body,$from_ta);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function getPlatform() {
        $stmt = $this->conn->prepare(
            "SELECT pa.*,u.name AS author FROM platform_announcements pa JOIN users u ON pa.author_id=u.id ORDER BY pa.created_at DESC"
        );
        $stmt->execute(); $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function createPlatform($author_id,$title,$body) {
        $stmt = $this->conn->prepare("INSERT INTO platform_announcements(author_id,title,body) VALUES(?,?,?)");
        $stmt->bind_param('iss',$author_id,$title,$body);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }
}