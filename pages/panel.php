<?php

include "../backend/db_connection.php";
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
switch ($userRole) {
    case "student":
        $roleShow = '<li><a href="s-roomSchedule.php">View Room Schedule</a></li>';
        $roleText = 'Student';
        break;
    case "teacher":
        $roleShow = '<li><a href="t-roomSelector.php">Room Selector</a></li>
                    <li><a href="t-scheduleManager.php">Schedule Manager</a></li>';
        $roleText = 'Teacher';
        break;
    case "admin":
        $roleShow = '<li><a href="a-userManager.php">User Manager</a></li>
                    <li><a href="a-roomManager.php">Room Manager</a></li>
                    <li><a href="a-maintenance.php">Maintenance Scheduler</a></li>
                    <li><a href="a-report.php">Report Generator</a></li>
                    <li><a href="a-conflict.php">Conflict Resolver</a></li>';
        $roleText = 'Admin';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="../css/main.css">
    <title><?php echo $pageTitle; ?></title>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleNav() {
            const nav = document.querySelector("nav");
            nav.classList.toggle("active");
        }
    </script>
</head>

<body>
    <header>
        <button id="nav-toggle-btn" onclick="toggleNav()"><i class="fa-solid fa-bars fa-2xl"></i></button>
        <div id="headerTitle">
            <img src="../images/NTC-logo.png" alt="" id="NTC-logo">
            <h1>Classroom Resource Management</h1>
        </div>
        <div><button id="logout-btn" onclick="logout()">Logout</button></div>
    </header>
    <div id="main-container">
        <nav>
            <ul>
                <li>
                    <div>
                        <div class="nav-bullet"></div><a href="dashboard.php">Dashboard</a>
                    </div>
                </li>
                <li>
                    <div>
                        <div class="nav-bullet"></div>
                        <a href="aboutUs.php">About Us</a>
                    </div>
                    <ul>
                        <li><a href="aboutUs.php#our-story">Our Story</a></li>
                        <li><a href="aboutUs.php#our-team">Our Team</a></li>
                        <li><a href="aboutUs.php#contact">Connect with Us</a></li>
                        <li><a href="aboutUs.php#terms">Terms and Policies</a></li>
                    </ul>
                </li>
                <li>
                    <div>
                        <div class="nav-bullet"></div>
                        <div><?php echo $roleText; ?></div>
                    </div>
                    <ul>
                        <?php echo $roleShow; ?>
                    </ul>
                </li>
            </ul>
        </nav>