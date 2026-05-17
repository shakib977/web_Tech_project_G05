<?php
// models/DoubtSession.php — SHARED (All Members)
class DoubtSession {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getByTA($ta_id) {
        $stmt=$this->conn->prepare(
            "SELECT ds.*,c.title AS course_title,(SELECT COUNT(*) FROM doubt_session_bookings WHERE doubt_session_id=ds.id) AS bookings
             FROM doubt_sessions ds JOIN courses c ON ds.course_id=c.id WHERE ds.ta_id=? ORDER BY ds.scheduled_at DESC"
        );
        $stmt->bind_param('i',$ta_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getUpcomingForCourses(array $course_ids, $student_id) {
        if (empty($course_ids)) return [];
        $ph=implode(',',array_fill(0,count($course_ids),'?'));
        $types=str_repeat('i',count($course_ids));
        $stmt=$this->conn->prepare(
            "SELECT ds.*,c.title AS course_title,u.name AS ta_name,
             (SELECT id FROM doubt_session_bookings WHERE doubt_session_id=ds.id AND student_id=?) AS my_booking_id,
             (SELECT COUNT(*) FROM doubt_session_bookings WHERE doubt_session_id=ds.id) AS booking_count
             FROM doubt_sessions ds JOIN courses c ON ds.course_id=c.id JOIN users u ON ds.ta_id=u.id
             WHERE ds.course_id IN ($ph) AND ds.is_cancelled=0 AND ds.scheduled_at > NOW() ORDER BY ds.scheduled_at"
        );
        $params=array_merge([$student_id],$course_ids);
        $stmt->bind_param('i'.$types,...$params); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function create($course_id,$ta_id,$title,$scheduled_at,$duration,$location,$max_attendees) {
        $stmt=$this->conn->prepare(
            "INSERT INTO doubt_sessions(course_id,ta_id,title,scheduled_at,duration_minutes,location_or_link,max_attendees) VALUES(?,?,?,?,?,?,?)"
        );
        $stmt->bind_param('iissisi',$course_id,$ta_id,$title,$scheduled_at,$duration,$location,$max_attendees);
        $ok=$stmt->execute(); $id=$this->conn->insert_id; $stmt->close(); return $ok ? $id : false;
    }

    public function cancel($id,$ta_id,$reason) {
        $stmt=$this->conn->prepare("UPDATE doubt_sessions SET is_cancelled=1,cancel_reason=? WHERE id=? AND ta_id=?");
        $stmt->bind_param('sii',$reason,$id,$ta_id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function book($session_id,$student_id) {
        $stmt=$this->conn->prepare("INSERT IGNORE INTO doubt_session_bookings(doubt_session_id,student_id) VALUES(?,?)");
        $stmt->bind_param('ii',$session_id,$student_id); $ok=$stmt->execute(); $stmt->close(); return $ok;
    }

    public function getBookings($session_id) {
        $stmt=$this->conn->prepare(
            "SELECT u.name,u.email,u.student_id,b.booked_at FROM doubt_session_bookings b JOIN users u ON b.student_id=u.id WHERE b.doubt_session_id=? ORDER BY b.booked_at"
        );
        $stmt->bind_param('i',$session_id); $stmt->execute();
        $r=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }
}