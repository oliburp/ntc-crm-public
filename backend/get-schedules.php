<?php
include 'db_connection.php';

if (isset($_GET['room_id'])) {
    $roomId = $_GET['room_id'];
    $weekOffset = isset($_GET['weekOffset']) ? (int)$_GET['weekOffset'] : 0;

    $startOfWeek = date('Y-m-d', strtotime("this week +$weekOffset week"));
    $endOfWeek = date('Y-m-d', strtotime("this week +$weekOffset week +6 days"));

    $sql = "SELECT subject, start_time, end_time, course_year 
            FROM schedules 
            WHERE room_id = ? 
            AND start_time BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $roomId, $startOfWeek, $endOfWeek);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedules = [];
    while ($schedule = $result->fetch_assoc()) {
        $schedules[] = $schedule;
    }

    echo json_encode($schedules);
}
?>
