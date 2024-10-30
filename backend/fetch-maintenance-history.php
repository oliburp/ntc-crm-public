<?php
include 'db_connection.php'; // Include your DB connection

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Correct SQL query according to your table structure
$sql = "SELECT * FROM maintenance_history ORDER BY changed_at DESC";
$result = $conn->query($sql);

$history = [];
if ($result) {
    // Check if there are results
    if ($result->num_rows > 0) {
        // Fetch each row and build the history array
        while ($row = $result->fetch_assoc()) {
            
            $DateTime = new DateTime($row['changed_at']);
            $DayTime = $DateTime->format('M. d, Y');

            $history[] = [
                'id' => $row['id'],
                'maintenance_id' => $row['maintenance_id'],  // Correct typo here
                'label' => $row['label'],
                'room_code' => $row['room_code'],
                'changed_at' => $DayTime,
                'status' => $row['status'],
            ];
        }
    } else {
        // No records found
        echo json_encode(['error' => 'No maintenance history found']);
        exit();
    }
} else {
    // Log query error and return JSON with error message
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit();
}

// Return the result as JSON
$conn->close();
echo json_encode($history);
