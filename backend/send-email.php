<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include "db_connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit();
}

// Collect POST data
$email = $_POST['email'];
$conflict_room_id = $_POST['conflict_room'];
$available_rooms = $_POST['available_rooms'];
$additional_message = isset($_POST['additional_message']) ? $_POST['additional_message'] : "";

// Validate that required fields are filled
if (empty($email) || empty($conflict_room_id) || empty($available_rooms)) {
    echo json_encode(["error" => "Required fields are missing."]);
    exit();
}

// Fetch conflict room code
$sql_conflict = "SELECT room_code FROM rooms WHERE room_id = ?";
$stmt_conflict = $conn->prepare($sql_conflict);
$stmt_conflict->bind_param("i", $conflict_room_id);
$stmt_conflict->execute();
$stmt_conflict->bind_result($conflict_room_code);
$stmt_conflict->fetch();
$stmt_conflict->close();

// Fetch available room codes
$available_room_codes = [];
foreach ($available_rooms as $room_id) {
    $sql_available = "SELECT room_code FROM rooms WHERE room_id = ?";
    $stmt_available = $conn->prepare($sql_available);
    $stmt_available->bind_param("i", $room_id);
    $stmt_available->execute();
    $stmt_available->bind_result($room_code);
    $stmt_available->fetch();
    $available_room_codes[] = $room_code;
    $stmt_available->close();
}

// Create the email message
$subject = "Conflict Resolution for Room: " . $conflict_room_code;
$message = "There is a conflict with the following room: " . $conflict_room_code . "\n";
$message .= "The available rooms are: " . implode(", ", $available_room_codes) . "\n";
if (!empty($additional_message)) {
    $message .= "\nAdditional Message:\n" . $additional_message;
}

// Initialize PHPMailer
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // SMTP server (e.g., Gmail)
    $mail->SMTPAuth = true;
    $mail->Username = 'null'; // Your email
    $mail->Password = 'null'; // Your email password
    $mail->SMTPSecure = 'tls'; // Encryption (TLS or SSL)
    $mail->Port = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('no-reply@yourdomain.com', 'Conflict Resolution');
    $mail->addAddress($email); // Add recipient

    // Content
    $mail->isHTML(false); // Set email format to plain text
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Send the email
    if ($mail->send()) {
        header("Location: ../pages/a-conflict.php?status=success");
        exit();
    } else {
        header("Location: ../pages/a-conflict.php?status=fail");
        exit();
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to send email: " . $mail->ErrorInfo]);
}

$conn->close();
?>
