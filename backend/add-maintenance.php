<?php
include 'db_connection.php'; // Make sure to include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize an array to hold validation errors
    $errors = [];

    // Validate inputs
    $label = strtoupper(trim($_POST['label']));
    $room_id = (int)$_POST['room_id']; // Ensure room_id is an integer
    $maintenance_date = trim($_POST['maintenance_date']);
    $maintenance_status = strtoupper(trim($_POST['maintenance_status']));

    // Check if label is empty
    if (empty($label)) {
        $errors[] = 'Label is required.';
    }

    // Check if maintenance_date is a valid date
    if (empty($maintenance_date) || !DateTime::createFromFormat('Y-m-d', $maintenance_date)) {
        $errors[] = 'Please provide a valid maintenance date.';
    }

    // Validate maintenance_status
    $valid_statuses = ['PENDING', 'COMPLETED', 'DELETED'];
    if (!in_array($maintenance_status, $valid_statuses)) {
        $errors[] = 'Invalid maintenance status.';
    }

    // If there are validation errors, return them as JSON
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Check for existing maintenance on the same date for the specified room
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM room_maintenance WHERE room_id = ? AND maintenance_date = ? AND maintenance_status != 'deleted'");
    $check_stmt->bind_param("is", $room_id, $maintenance_date);
    $check_stmt->execute();
    $check_stmt->bind_result($existing_count);
    $check_stmt->fetch();
    $check_stmt->close();

    // If there are existing records, return an error response
    if ($existing_count > 0) {
        echo json_encode(['success' => false, 'error' => 'A maintenance record already exists for this room on the specified date.']);
        exit;
    }

    // Prepare and execute your insert statement
    $stmt = $conn->prepare("INSERT INTO room_maintenance (label, room_id, maintenance_date, maintenance_status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $label, $room_id, $maintenance_date, $maintenance_status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Maintenance record added successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
}
?>
