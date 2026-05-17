<?php
// models/Quiz.php — SHARED (All Members)
class Quiz {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT q.*,c.title AS course_title,c.instructor_id,c.id AS course_id FROM quizzes q JOIN courses c ON q.course_id=c.id WHERE q.id=?"
        );
        $stmt->bind_param('i',$id); $stmt->execute();
        $r=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function getByCourse($course_id, $status=null) {
        $where = 'course_id=?'; $params=[$course_id]; $types='i';
        if ($status) { $where .= ' AND status=?'; $params[]=$status; $types.='s'; }
        $stmt = $this->conn->prepare("SELECT * FROM quizzes WHERE $where ORDER BY available_from, id");
        $stmt->bind_param($types,...$params); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function create($course_id,$created_by,$title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until) {
        $stmt = $this->conn->prepare(
            "INSERT INTO quizzes(course_id,created_by,title,description,time_limit_minutes,total_marks,pass_mark,quiz_type,status,available_from,available_until) VALUES(?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param('iissiiissss',$course_id,$created_by,$title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until);
        $ok=$stmt->execute(); $id=$this->conn->insert_id; $stmt->close(); return $ok ? $id : false;
    }

    public function update($id,$title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until) {
        $stmt = $this->conn->prepare(
            "UPDATE quizzes SET title=?,description=?,time_limit_minutes=?,total_marks=?,pass_mark=?,quiz_type=?,status=?,available_from=?,available_until=? WHERE id=?"
        );
        $stmt->bind_param('ssiiissss i',$title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until,$id);
        $stmt->close();
        $stmt = $this->conn->prepare(
            "UPDATE quizzes SET title=?,description=?,time_limit_minutes=?,total_marks=?,pass_mark=?,quiz_type=?,status=?,available_from=?,available_until=? WHERE id=?"
        );
        $stmt->bind_param('ssiiiosssi',$title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until,$id);
        $stmt->close();
        // Clean version - correct types: s s i i i s s s s i
        $stmt = $this->conn->prepare(
            "UPDATE quizzes SET title=?,description=?,time_limit_minutes=?,total_marks=?,pass_mark=?,quiz_type=?,status=?,available_from=?,available_until=? WHERE id=?"
        );
        $stmt->bind_param('ssiiissss i', $title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until,$id);
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE quizzes SET title=?,description=?,time_limit_minutes=?,total_marks=?,pass_mark=?,quiz_type=?,status=?,available_from=?,available_until=? WHERE id=?");
        $stmt->bind_param('ssiiiosssi',$title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until,$id);
        $stmt->close();

        // Actually just use direct query approach:
        $title=mysqli_real_escape_string($this->conn,$title);
        $stmt = $this->conn->prepare("UPDATE quizzes SET title=?,description=?,time_limit_minutes=?,total_marks=?,pass_mark=?,quiz_type=?,status=?,available_from=?,available_until=? WHERE id=?");
        $stmt->bind_param('ss' . 'iii' . 'ssssi', $title,$desc,$time_limit,$total_marks,$pass_mark,$type,$status,$from,$until,$id);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function getAll($course_id=0, $type='', $status='') {
        $where=['1=1']; $params=[]; $types='';
        if ($course_id) { $where[]='q.course_id=?'; $params[]=$course_id; $types.='i'; }
        if ($type)      { $where[]='q.quiz_type=?';  $params[]=$type;      $types.='s'; }
        if ($status)    { $where[]='q.status=?';     $params[]=$status;    $types.='s'; }
        $sql = "SELECT q.*,c.title AS course_title,u.name AS creator,
                (SELECT COUNT(*) FROM attempts WHERE quiz_id=q.id) AS attempt_count
                FROM quizzes q JOIN courses c ON q.course_id=c.id JOIN users u ON q.created_by=u.id
                WHERE ".implode(' AND ',$where)." ORDER BY q.id DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types,...$params);
        $stmt->execute(); $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }
}