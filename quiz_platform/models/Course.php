<?php
// models/Course.php — SHARED (All Members)
class Course {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT c.*,s.name AS subject,u.name AS instructor_name,u.email AS instructor_email,
             (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count
             FROM courses c JOIN subjects s ON c.subject_id=s.id JOIN users u ON c.instructor_id=u.id WHERE c.id=?"
        );
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function getActive($subject_id=0, $search='') {
        $where = ["c.status='active'"]; $params=[]; $types='';
        if ($subject_id) { $where[]='c.subject_id=?'; $params[]=$subject_id; $types.='i'; }
        if ($search) { $like='%'.$search.'%'; $where[]='(c.title LIKE ? OR u.name LIKE ?)'; $params=array_merge($params,[$like,$like]); $types.='ss'; }
        $sql = "SELECT c.*,s.name AS subject,u.name AS instructor,
                (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count
                FROM courses c JOIN subjects s ON c.subject_id=s.id JOIN users u ON c.instructor_id=u.id
                WHERE ".implode(' AND ',$where)." ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types,...$params);
        $stmt->execute(); $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getByInstructor($instructor_id) {
        $stmt = $this->conn->prepare(
            "SELECT c.*,s.name AS subject,
             (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count,
             (SELECT COUNT(*) FROM quizzes WHERE course_id=c.id) AS quiz_count,
             (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='pending') AS pending_count
             FROM courses c JOIN subjects s ON c.subject_id=s.id WHERE c.instructor_id=? ORDER BY c.created_at DESC"
        );
        $stmt->bind_param('i',$instructor_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getByTA($ta_id) {
        $stmt = $this->conn->prepare(
            "SELECT c.*,s.name AS subject,u.name AS instructor_name,
             (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count
             FROM course_tas ct JOIN courses c ON ct.course_id=c.id
             JOIN subjects s ON c.subject_id=s.id JOIN users u ON c.instructor_id=u.id
             WHERE ct.ta_id=? ORDER BY c.title"
        );
        $stmt->bind_param('i',$ta_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getEnrolledByStudent($student_id) {
        $stmt = $this->conn->prepare(
            "SELECT c.*,s.name AS subject,u.name AS instructor_name,e.status AS enrollment_status,e.enrolled_at,
             (SELECT COUNT(*) FROM quizzes WHERE course_id=c.id AND status='published') AS published_quizzes
             FROM enrollments e JOIN courses c ON e.course_id=c.id
             JOIN subjects s ON c.subject_id=s.id JOIN users u ON c.instructor_id=u.id
             WHERE e.student_id=? AND e.status='active' ORDER BY e.enrolled_at DESC"
        );
        $stmt->bind_param('i',$student_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function create($instructor_id,$subject_id,$title,$description,$enrollment_type,$max_students,$status) {
        $stmt = $this->conn->prepare(
            "INSERT INTO courses(instructor_id,subject_id,title,description,enrollment_type,max_students,status) VALUES(?,?,?,?,?,?,?)"
        );
        $stmt->bind_param('iisssis',$instructor_id,$subject_id,$title,$description,$enrollment_type,$max_students,$status);
        $ok=$stmt->execute(); $id=$this->conn->insert_id; $stmt->close(); return $ok ? $id : false;
    }

    public function update($id,$subject_id,$title,$description,$enrollment_type,$max_students,$status) {
        $stmt = $this->conn->prepare(
            "UPDATE courses SET subject_id=?,title=?,description=?,enrollment_type=?,max_students=?,status=? WHERE id=?"
        );
        $stmt->bind_param('issisis',$subject_id,$title,$description,$enrollment_type,$max_students,$status,$id);
        // fix: i s s s i s i
        $stmt->close();
        $stmt = $this->conn->prepare(
            "UPDATE courses SET subject_id=?,title=?,description=?,enrollment_type=?,max_students=?,status=? WHERE id=?"
        );
        $stmt->bind_param('isssisi',$subject_id,$title,$description,$enrollment_type,$max_students,$status,$id);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function getAll($subject_id=0,$status='') {
        $where=['1=1']; $params=[]; $types='';
        if ($subject_id) { $where[]='c.subject_id=?'; $params[]=$subject_id; $types.='i'; }
        if ($status)     { $where[]='c.status=?';     $params[]=$status;     $types.='s'; }
        $sql = "SELECT c.*,s.name AS subject,u.name AS instructor,
                (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id AND status='active') AS enrolled_count
                FROM courses c JOIN subjects s ON c.subject_id=s.id JOIN users u ON c.instructor_id=u.id
                WHERE ".implode(' AND ',$where)." ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types,...$params);
        $stmt->execute(); $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }
}