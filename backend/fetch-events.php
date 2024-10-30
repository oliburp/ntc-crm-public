<?php
session_start(); 
include "db_connection.php"; 

$user_id = $_SESSION['user_id'];

$sql = "SELECT schedules.schedule_id, schedules.subject, schedules.course_year, schedules.start_time, schedules.end_time, rooms.room_code
        FROM schedules
        JOIN rooms ON schedules.room_id = rooms.room_id
        WHERE schedules.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id); 
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['schedule_id'],
        'title' => $row['subject'],
        'course_year' => $row['course_year'],
        'start' => $row['start_time'],
        'end' => $row['end_time'],
        'extendedProps' => [
            'room_code' => $row['room_code'] 
        ]
    ];
}

echo json_encode($events);
?>
