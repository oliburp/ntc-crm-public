<?php
include "db_connection.php"; // Include your database connection

if (isset($_POST['searchTerm'])) {
    $searchTerm = $_POST['searchTerm'];
    $sql = "SELECT * FROM users WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTermParam = "%" . $searchTerm . "%";
    $stmt->bind_param("sss", $searchTermParam, $searchTermParam, $searchTermParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($users); // Return results as JSON
}
?>
