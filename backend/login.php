<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT user_id, username, password, role FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            $logQuery = "INSERT INTO user_activity_log (user_id, login_time) VALUES (?, NOW())";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("i", $user['user_id']);
            $logStmt->execute();

            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "redirect" => "../pages/dashboard.php"]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No user found with that username!"]);
    }
}
