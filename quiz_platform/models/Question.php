<?php
// models/Question.php — SHARED (All Members)
class Question {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getByQuiz($quiz_id) {
        $stmt = $this->conn->prepare("SELECT * FROM questions WHERE quiz_id=? ORDER BY order_index, id");
        $stmt->bind_param('i',$quiz_id); $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        foreach ($questions as &$q) {
            $stmt2 = $this->conn->prepare("SELECT * FROM options WHERE question_id=?");
            $stmt2->bind_param('i',$q['id']); $stmt2->execute();
            $q['options'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC); $stmt2->close();
        }
        return $questions;
    }

    public function create($quiz_id, $text, $marks, $created_by) {
        $stmt = $this->conn->prepare("INSERT INTO questions(quiz_id,question_text,marks,created_by) VALUES(?,?,?,?)");
        $stmt->bind_param('isii',$quiz_id,$text,$marks,$created_by);
        $ok=$stmt->execute(); $id=$this->conn->insert_id; $stmt->close(); return $ok ? $id : false;
    }

    public function addOption($question_id, $text, $is_correct) {
        $stmt = $this->conn->prepare("INSERT INTO options(question_id,option_text,is_correct) VALUES(?,?,?)");
        $stmt->bind_param('isi',$question_id,$text,$is_correct);
        $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM questions WHERE id=?");
        $stmt->bind_param('i',$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }
}