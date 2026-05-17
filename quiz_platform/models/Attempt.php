<?php
// models/Attempt.php — SHARED (All Members)
class Attempt {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function create($quiz_id, $student_id) {
        $stmt = $this->conn->prepare("INSERT INTO attempts(quiz_id,student_id) VALUES(?,?)");
        $stmt->bind_param('ii',$quiz_id,$student_id);
        $ok=$stmt->execute(); $id=$this->conn->insert_id; $stmt->close(); return $ok ? $id : false;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT a.*,q.title,q.total_marks,q.pass_mark,q.quiz_type,c.title AS course_title,c.id AS course_id
             FROM attempts a JOIN quizzes q ON a.quiz_id=q.id JOIN courses c ON q.course_id=c.id WHERE a.id=?"
        );
        $stmt->bind_param('i',$id); $stmt->execute();
        $r=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function getIncomplete($quiz_id, $student_id) {
        $stmt = $this->conn->prepare(
            "SELECT id FROM attempts WHERE quiz_id=? AND student_id=? AND completed_at IS NULL ORDER BY started_at DESC LIMIT 1"
        );
        $stmt->bind_param('ii',$quiz_id,$student_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function complete($id, $score) {
        $stmt = $this->conn->prepare("UPDATE attempts SET score=?,completed_at=NOW(),is_graded=1 WHERE id=?");
        $stmt->bind_param('di',$score,$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function saveAnswer($attempt_id, $question_id, $option_id) {
        $stmt = $this->conn->prepare(
            "INSERT INTO answers(attempt_id,question_id,selected_option_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE selected_option_id=?"
        );
        $stmt->bind_param('iiii',$attempt_id,$question_id,$option_id,$option_id);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function getByStudent($student_id, $course_id=0) {
        $where = 'a.student_id=? AND a.completed_at IS NOT NULL';
        $params = [$student_id]; $types='i';
        if ($course_id) { $where .= ' AND q.course_id=?'; $params[]=$course_id; $types.='i'; }
        $stmt = $this->conn->prepare(
            "SELECT a.*,q.title AS quiz_title,q.total_marks,q.pass_mark,q.quiz_type,c.title AS course_title
             FROM attempts a JOIN quizzes q ON a.quiz_id=q.id JOIN courses c ON q.course_id=c.id
             WHERE $where ORDER BY a.completed_at DESC"
        );
        $stmt->bind_param($types,...$params); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getByQuiz($quiz_id) {
        $stmt = $this->conn->prepare(
            "SELECT a.*,u.name,u.student_id,TIMESTAMPDIFF(SECOND,a.started_at,a.completed_at) AS duration_sec
             FROM attempts a JOIN users u ON a.student_id=u.id
             WHERE a.quiz_id=? AND a.completed_at IS NOT NULL ORDER BY a.score DESC"
        );
        $stmt->bind_param('i',$quiz_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }
}