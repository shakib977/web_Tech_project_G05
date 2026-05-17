<?php
// models/Material.php — SHARED (All Members)
class Material {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getByCourse($course_id) {
        $stmt = $this->conn->prepare("SELECT * FROM course_materials WHERE course_id=? ORDER BY created_at DESC");
        $stmt->bind_param('i',$course_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function create($course_id,$uploaded_by,$title,$file_path,$type) {
        $stmt = $this->conn->prepare("INSERT INTO course_materials(course_id,uploaded_by,title,file_path,material_type) VALUES(?,?,?,?,?)");
        $stmt->bind_param('iisss',$course_id,$uploaded_by,$title,$file_path,$type);
        $ok=$stmt->execute(); $id=$this->conn->insert_id; $stmt->close(); return $ok ? $id : false;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("SELECT file_path,material_type FROM course_materials WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $m=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($m && $m['material_type']!=='link' && $m['file_path']) {
            $path = __DIR__.'/../uploads/materials/'.$m['file_path'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $this->conn->prepare("DELETE FROM course_materials WHERE id=?");
        $stmt->bind_param('i',$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }
}