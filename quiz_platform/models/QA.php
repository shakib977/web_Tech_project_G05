<?php
// models/QA.php — SHARED (All Members)
class QA {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getQuestionsByCourse($course_id) {
        $stmt = $this->conn->prepare(
            "SELECT qq.*,u.name AS student_name FROM qa_questions qq JOIN users u ON qq.student_id=u.id WHERE qq.course_id=? ORDER BY qq.is_resolved ASC, qq.created_at DESC"
        );
        $stmt->bind_param('i',$course_id); $stmt->execute();
        $questions=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        foreach ($questions as &$q) {
            $stmt2=$this->conn->prepare(
                "SELECT qa.*,u.name AS author_name,u.role AS author_role FROM qa_answers qa JOIN users u ON qa.author_id=u.id WHERE qa.qa_question_id=? ORDER BY qa.is_endorsed DESC, qa.created_at"
            );
            $stmt2->bind_param('i',$q['id']); $stmt2->execute();
            $q['answers']=$stmt2->get_result()->fetch_all(MYSQLI_ASSOC); $stmt2->close();
        }
        return $questions;
    }

    public function postQuestion($course_id,$student_id,$title,$body) {
        $stmt=$this->conn->prepare("INSERT INTO qa_questions(course_id,student_id,title,body) VALUES(?,?,?,?)");
        $stmt->bind_param('iiss',$course_id,$student_id,$title,$body);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function postAnswer($qa_question_id,$author_id,$body) {
        $stmt=$this->conn->prepare("INSERT INTO qa_answers(qa_question_id,author_id,body) VALUES(?,?,?)");
        $stmt->bind_param('iis',$qa_question_id,$author_id,$body);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function markResolved($id,$student_id) {
        $stmt=$this->conn->prepare("UPDATE qa_questions SET is_resolved=1 WHERE id=? AND student_id=?");
        $stmt->bind_param('ii',$id,$student_id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function endorseAnswer($id) {
        $stmt=$this->conn->prepare("UPDATE qa_answers SET is_endorsed=1 WHERE id=?");
        $stmt->bind_param('i',$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }
}