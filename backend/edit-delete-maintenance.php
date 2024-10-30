<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenance_id = $_POST['maintenance_id'];
    $action = $_POST['action'];

    if ($action === 'edit') {
        // Call the edit function
        editMaintenance($maintenance_id, $_POST);
    } elseif ($action === 'delete') {
        // Call the delete function
        deleteMaintenance($maintenance_id);
    } elseif ($action === 'mark_done') {
        // Call the mark done function
        markMaintenanceAsDone($maintenance_id);
    }
}

// Function to edit maintenance record
function editMaintenance($maintenance_id, $data)
{
    global $conn;
    // Convert inputs to uppercase
    $label = strtoupper($data['label']);
    $room_id = $data['room_id'];
    $maintenance_date = $data['maintenance_date'];
    $maintenance_status = strtoupper($data['maintenance_status']); // If needed

    $stmt = $conn->prepare("UPDATE room_maintenance SET room_id = ?, maintenance_date = ?, maintenance_status = ?, label = ? WHERE maintenance_id = ?");
    $stmt->bind_param("isssi", $room_id, $maintenance_date, $maintenance_status, $label, $maintenance_id);

    if ($stmt->execute()) {
        // Log the edit operation
        logMaintenanceHistory($maintenance_id, $label, $room_id, 'edited');
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
}

// Function to delete maintenance record
function deleteMaintenance($maintenance_id)
{
    global $conn;

    // Step 1: Fetch details to log
    $log_stmt = $conn->prepare("SELECT label, room_id FROM room_maintenance WHERE maintenance_id = ?");
    $log_stmt->bind_param("i", $maintenance_id);
    $log_stmt->execute();
    $log_stmt->bind_result($label, $room_id);

    if ($log_stmt->fetch()) {
        // Close the log statement after fetching
        $log_stmt->close();

        // Step 2: Log to maintenance_history
        $room_code = getRoomCode($room_id, $conn); // Fetch the room code for logging
        $log_status = 'deleted';

        $history_stmt = $conn->prepare("INSERT INTO maintenance_history (maintenance_id, label, room_code, status) VALUES (?, ?, ?, ?)");
        $history_stmt->bind_param("isss", $maintenance_id, $label, $room_code, $log_status);

        if (!$history_stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Failed to log deletion: ' . $history_stmt->error]);
            return;
        }

        $history_stmt->close();

        // Step 3: Now delete the record from room_maintenance
        $stmt = $conn->prepare("UPDATE room_maintenance SET maintenance_status = 'deleted' WHERE maintenance_id = ?");
        $stmt->bind_param("i", $maintenance_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete record: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'No record found for deletion.']);
    }
}

// Function to mark maintenance as done
function markMaintenanceAsDone($maintenance_id)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE room_maintenance SET maintenance_status = 'completed' WHERE maintenance_id = ?");
    $stmt->bind_param("i", $maintenance_id);

    if ($stmt->execute()) {
        // Fetch details for logging
        $log_stmt = $conn->prepare("SELECT label, room_id FROM room_maintenance WHERE maintenance_id = ?");
        $log_stmt->bind_param("i", $maintenance_id);
        $log_stmt->execute();
        $log_stmt->bind_result($label, $room_id);

        if ($log_stmt->fetch()) {
            $log_stmt->close();
            // Log the done operation
            logMaintenanceHistory($maintenance_id, $label, $room_id, 'completed');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No record found for marking as done.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
}

// Function to log maintenance history
function logMaintenanceHistory($maintenance_id, $label, $room_id, $log_status)
{
    global $conn;
    $room_code = getRoomCode($room_id, $conn);

    // Convert the label to uppercase before logging
    $label = strtoupper($label);
    $log_status = strtoupper($log_status); // If needed

    $history_stmt = $conn->prepare("INSERT INTO maintenance_history (maintenance_id, label, room_code, status) VALUES (?, ?, ?, ?)");
    $history_stmt->bind_param("isss", $maintenance_id, $label, $room_code, $log_status);
    $history_stmt->execute();
    $history_stmt->close();
}

// Function to get room_code based on room_id
function getRoomCode($room_id, $conn)
{
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

$conn->close();
