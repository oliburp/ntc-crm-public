<?php
include 'db_connection.php'; // Include your DB connection

$sql = "SELECT room_maintenance.*, rooms.room_code 
        FROM room_maintenance
        JOIN rooms ON room_maintenance.room_id = rooms.room_id
        WHERE room_maintenance.maintenance_status != 'deleted'";

$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['maintenance_id'],
        'title' => $row['label'],
        'start' => $row['maintenance_date'],
        'extendedProps' => [
            'room_code' => $row['room_code'],
            'maintenance_status' => $row['maintenance_status']
        ]
    ];
}

// Debugging line to see fetched events
file_put_contents('debug.log', print_r($events, true));

echo json_encode($events);
?>
