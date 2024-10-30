<?php
include "db_connection.php";

if (isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];
    $today = date("Y-m-d");

    // Fetch all schedules for the room on today’s date
    $stmt = $conn->prepare("SELECT r.room_code, s.subject, s.course_year, DATE_FORMAT(s.start_time, '%h:%i %p') as start_time, DATE_FORMAT(s.end_time, '%h:%i %p') as end_time FROM schedules s JOIN rooms r ON s.room_id = r.room_id WHERE s.room_id = ? AND DATE(s.start_time) = ?");
    $stmt->bind_param("is", $room_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }

    // Return schedules as JSON
    echo json_encode($schedules);
}
