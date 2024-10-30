<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Check if the event is part of a recurring schedule
    $sql = "SELECT COUNT(*) as count FROM schedules WHERE schedule_id = ? OR (subject = ? AND room_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $id, $subject, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 1) {
        // Delete all instances of the recurring event
        $sql = "DELETE FROM schedules WHERE subject = ? AND room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $subject, $room_id);
    } else {
        // Delete single event
        $sql = "DELETE FROM schedules WHERE schedule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete event']);
    }

    $stmt->close();
    $conn->close();
}
