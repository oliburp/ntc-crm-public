<?php
session_start();
include "db_connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit();
}

// Collect POST data
$user_id = $_POST['user_id'];
$room_id = $_POST['room_id'];
$subject = $_POST['subject'];
$course_year = $_POST['course_year'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$repeat_weekly = $_POST['repeat_weekly'];

// Check for conflicting schedules
$sql_conflict = "SELECT * FROM schedules WHERE room_id = ? AND (
    (start_time <= ? AND end_time > ?) OR 
    (start_time < ? AND end_time >= ?)
)";
$stmt_conflict = $conn->prepare($sql_conflict);

$adjusted_end_time = strtotime($end_time) - 60;
$adjusted_end_time = date('Y-m-d H:i:s', $adjusted_end_time);

$stmt_conflict->bind_param("issss", $room_id, $start_time, $end_time, $end_time, $adjusted_end_time);
$stmt_conflict->execute();
$result_conflict = $stmt_conflict->get_result();

if ($result_conflict->num_rows > 0) {
    $conflicting_schedule = $result_conflict->fetch_assoc();
    $startDateTime = new DateTime($conflicting_schedule['start_time']);
    $endDateTime = new DateTime($conflicting_schedule['end_time']);
    $startDay = $startDateTime->format('l');
    $startTime = $startDateTime->format('h:i A');
    $endTime = $endDateTime->format('h:i A');
    $room_code = getRoomCode($conflicting_schedule['room_id'], $conn);
    
    $conflict_message = "Conflict detected with existing schedule: Room " .
        $room_code . " - " . $conflicting_schedule['course_year'] .
        " (" . $startDay . ", " . $startTime . " - " . $endTime . ")";
    echo json_encode(["error" => $conflict_message]);
} else {
    // Insert the new schedule and repeat weekly if necessary
    if ($repeat_weekly) {
        $recurrence_limit = 4; 
    } else {
        $recurrence_limit = 0; 
    }

    for ($i = 0; $i <= $recurrence_limit; $i++) {
        $adjusted_start_time = (new DateTime($start_time))->modify("+$i week")->format('Y-m-d H:i:s');
        $adjusted_end_time = (new DateTime($end_time))->modify("+$i week")->format('Y-m-d H:i:s');

        // Check for conflicts for each recurring week
        $stmt_conflict->bind_param("issss", $room_id, $adjusted_start_time, $adjusted_start_time, $adjusted_end_time, $adjusted_end_time);
        $stmt_conflict->execute();
        $result_conflict = $stmt_conflict->get_result();

        if ($result_conflict->num_rows == 0) {
            // Insert the schedule if no conflict
            $sql = "INSERT INTO schedules (user_id, room_id, subject, course_year, start_time, end_time) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $user_id, $room_id, $subject, $course_year, $adjusted_start_time, $adjusted_end_time);
            if (!$stmt->execute()) {
                echo json_encode(["error" => "Failed to add event: " . $stmt->error]);
                $stmt->close();
                exit();
            }
            $stmt->close();
        }
    }

    if ($repeat_weekly) {
        echo json_encode(["success" => "Event added successfully, including weekly repeats."]);
    } else {
        echo json_encode(["success" => "Event added successfully"]);
    }
}

$stmt_conflict->close();
$conn->close();

function getRoomCode($room_id, $conn) {
    // Initialize the variable to avoid undefined variable error
    $room_code = null;

    $stmt = $conn->prepare("SELECT room_code FROM rooms WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->bind_result($room_code);

    // Fetch the result; if no result, $room_code will remain null
    if (!$stmt->fetch()) {
        $room_code = null;  // Explicitly set to null if no row is found
    }

    $stmt->close();
    return $room_code;  // Return null if no room is found
}
?>
