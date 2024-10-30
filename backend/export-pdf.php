<?php
require('fpdf/fpdf.php');
include 'db_connection.php'; // Include your database connection

function fetchRoomUsage()
{
    global $conn;
    $sql = "SELECT 
                room_code, 
                COUNT(*) AS usage_count, 
                COUNT(DISTINCT schedules.user_id) AS user_count,
                SUM(TIMESTAMPDIFF(HOUR, schedules.start_time, schedules.end_time)) AS total_hours_spent 
            FROM schedules 
            JOIN rooms ON schedules.room_id = rooms.room_id 
            WHERE DATE(schedules.start_time) = CURDATE() 
            GROUP BY room_code";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch user activity data
function fetchUserActivity()
{
    global $conn;
    $sql = "SELECT users.username, COUNT(user_activity_log.user_id) AS login_count,
                   CONCAT(
                       UPPER(SUBSTRING(users.role, 1, 1)),
                       LOWER(SUBSTRING(users.role, 2, LENGTH(users.role)))
                   ) AS role
            FROM users 
            LEFT JOIN user_activity_log ON users.user_id = user_activity_log.user_id 
            WHERE DATE(user_activity_log.login_time) = CURDATE()
            GROUP BY users.username";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch maintenance schedule data
function fetchMaintenanceSchedule()
{
    global $conn;
    $sql = "SELECT room_code, maintenance_date, label, maintenance_status 
            FROM room_maintenance 
            JOIN rooms ON room_maintenance.room_id = rooms.room_id 
            WHERE maintenance_date = CURDATE()";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$roomUsage = fetchRoomUsage();
$userActivity = fetchUserActivity();
$maintenanceSchedule = fetchMaintenanceSchedule();

$login_count = 0;
foreach ($userActivity as $activity) {
    $login_count += $activity['login_count'];
}
$room_sched_count = 0;
foreach ($roomUsage as $room) {
    $room_sched_count += $room['usage_count'];
}
$done = 0;
$delete = 0;
foreach ($maintenanceSchedule as $maintenance) {
    if ($maintenance['maintenance_status'] == 'COMPLETED') {
        $done += 1;
    } elseif ($maintenance['maintenance_status'] == 'DELETED') {
        $delete += 1;
    }
}

$reportType = $_GET['type'];
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

switch ($reportType) {
    case 'room_usage':
        // Title and Headers
        $pdf->Cell(0, 10, 'Reports total for: ' . date("m/d/Y"), 0, 1, 'LR');
        $pdf->Cell(0, 20, 'Room Usage Report', 0, 1, 'C');
        $pdf->Cell(0, 1, 'Active users:', 0, 1,);
        $pdf->Ln();
        $pdf->Cell(0, 10, $room_sched_count, 0, 1,);
        $pdf->Cell(60, 10, 'Room Code', 1);
        $pdf->Cell(60, 10, 'Number of Users', 1);
        $pdf->Cell(60, 10, 'Total Spent Hours', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 12);

        // Rows
        foreach ($roomUsage as $row) {
            $pdf->Cell(60, 10, strtoupper($row['room_code']), 1);
            $pdf->Cell(60, 10, $row['user_count'], 1);
            $pdf->Cell(60, 10, $row['total_hours_spent'], 1);
            $pdf->Ln();
        }
        break;

    case 'user_activity':
        // Title and Headers
        $pdf->Cell(0, 10, 'Reports total for: ' . date("m/d/Y"), 0, 1, 'LR');
        $pdf->Cell(0, 20, 'User Activity Report', 0, 1, 'C');
        $pdf->Cell(90, 1, 'All room schedule:', 0);
        $pdf->Cell(90, 1, 'Total website logins:', 0);
        $pdf->Ln();
        $pdf->Cell(90, 10, count($userActivity), 0);
        $pdf->Cell(90, 10, $login_count, 0,1);
        $pdf->Cell(90, 10, 'Username', 1);
        $pdf->Cell(90, 10, 'Role', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 12);

        // Rows
        foreach ($userActivity as $row) {
            $pdf->Cell(90, 10, strtoupper($row['username']), 1);
            $pdf->Cell(90, 10, $row['role'], 1);
            $pdf->Ln();
        }
        break;

    case 'maintenance_schedule':
        // Title and Headers
        $pdf->Cell(0, 10, 'Reports total for: ' . date("m/d/Y"), 0, 1, 'LR');
        $pdf->Cell(0, 20, 'Maintenance Schedule Report', 0, 1, 'C');
        $pdf->Cell(60, 10, 'Total maintenance', 0, 1);
        $pdf->Cell(60, 1, 'schedule:', 0);
        $pdf->Cell(60, 1, 'Successful:', 0);
        $pdf->Cell(60, 1, 'Cancelled:', 0);
        $pdf->Ln();
        $pdf->Cell(60, 10, count($maintenanceSchedule), 0);
        $pdf->Cell(60, 10, $done, 0);
        $pdf->Cell(60, 10, $delete, 0,1);
        $pdf->Cell(60, 10, 'Room Code', 1);
        $pdf->Cell(60, 10, 'Maintenance', 1);
        $pdf->Cell(60, 10, 'Date', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'I', 12);

        // Rows
        foreach ($maintenanceSchedule as $row) {
            $pdf->Cell(60, 10, strtoupper($row['room_code']), 1);
            $pdf->Cell(60, 10, strtoupper($row['label']), 1);
            $pdf->Cell(60, 10, $row['maintenance_date'], 1);
            $pdf->Ln();
        }
        break;

    default:
        $pdf->Cell(0, 10, 'Invalid report type', 0, 1, 'C');
        break;
}

// Output the PDF
$pdf->Output();
