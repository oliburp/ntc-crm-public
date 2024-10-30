<?php
session_start();
include "db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventId = $_POST['id'];
    $subject = $_POST['subject'];
    $room_id = $_POST['room_id'];
    $course_year = $_POST['course_year'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $repeat_weekly = isset($_POST['edit_repeat_weekly']) ? 1 : 0;

    // Validate the incoming data
    if (empty($eventId) || empty($subject) || empty($room_id) || empty($start_time) || empty($end_time)) {
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }

    // If not repeating, update the event
    if (!$repeat_weekly) {
        $sql = "UPDATE schedules SET subject = ?, room_id = ?, course_year = ?, start_time = ?, end_time = ? WHERE schedule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sisssi', $subject, $room_id, $course_year, $start_time, $end_time, $eventId);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Event updated successfully']);
        } else {
            echo json_encode(['error' => 'Failed to update event']);
        }
    } else {
        // Handle weekly update
        // First, delete existing occurrences for that event
        $sql = "DELETE FROM schedules WHERE schedule_id = ? OR (subject = ? AND room_id = ? AND start_time >= ? AND end_time <= ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('issss', $eventId, $subject, $room_id, $start_time, $end_time);
        $stmt->execute();

        // Insert the new updated schedule and repeat weekly
        $recurrence_limit = 4; // Limit for the number of weeks to add

        for ($i = 0; $i <= $recurrence_limit; $i++) {
            $adjusted_start_time = (new DateTime($start_time))->modify("+$i week")->format('Y-m-d H:i:s');
            $adjusted_end_time = (new DateTime($end_time))->modify("+$i week")->format('Y-m-d H:i:s');

            // Insert the schedule
            $sql = "INSERT INTO schedules (user_id, room_id, subject, course_year, start_time, end_time) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $user_id, $room_id, $subject, $course_year, $adjusted_start_time, $adjusted_end_time);
            $stmt->execute();
        }

        echo json_encode(["success" => "Event updated and scheduled for next weeks."]);
    }

    $stmt->close();
    $conn->close();
}
