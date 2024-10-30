<?php
$pageTitle = "Report Generator";
include "panel.php";

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
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Show the first tab by default
        $('#roomUsageReport').show();
        $('#userActivityReport').hide();
        $('#maintenanceScheduleReport').hide();

        $('#tabs li a').click(function(e) {
            e.preventDefault();
            const target = $(this).attr('href');

            $('#tabs li a').removeClass('active');
            $(this).addClass('active');

            $('#report-content > div').hide();
            $(target).show();
        });

        // Export PDF functions
        function exportPDF(reportType) {
            window.open(`../backend/export-pdf.php?type=${reportType}`, '_blank');
        }

        $('#exportRoomUsage').click(function() {
            exportPDF('room_usage');
        });
        $('#exportUserActivity').click(function() {
            exportPDF('user_activity');
        });
        $('#exportMaintenanceSchedule').click(function() {
            exportPDF('maintenance_schedule');
        });
    });
</script>
<main>
    <div class="m-4 p-4 w-100 mainReport">
        <ul class="nav nav-tabs" id="tabs">
            <li class="nav-item">
                <a class="nav-link active fs-3 text-dark" href="#roomUsageReport">Room Usage Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fs-3 text-dark" href="#userActivityReport">User Activity Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fs-3 text-dark" href="#maintenanceScheduleReport">Maintenance Schedule Report</a>
            </li>
        </ul>

        <div id="report-content">
            <!-- Room Usage Report -->
            <div id="roomUsageReport" class="report-tab p-3 mt-2">
                <h4>Reports total for: <?php date_default_timezone_set("Asia/Manila");
                                        echo date("Y/m/d") ?></h4>
                <div class="d-flex justify-content-evenly mb-4">
                    <div class="fs-4 d-flex flex-column align-items-center">
                        <div>All room schedule:</div>
                        <div><?php echo $room_sched_count; ?></div>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Room Code</th>
                            <th>Number of Users</th>
                            <th>Total Spent Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roomUsage as $usage): ?>
                            <tr>
                                <td><?php echo strtoupper($usage['room_code']); ?></td>
                                <td><?php echo $usage['user_count']; ?></td>
                                <td><?php echo $usage['total_hours_spent']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button id="exportRoomUsage" class="btn btn-primary">Export PDF</button>
            </div>

            <!-- User Activity Report -->
            <div id="userActivityReport" class="report-tab p-3 mt-2">
                <h4>Reports total for: <?php date_default_timezone_set("Asia/Manila");
                                        echo date("Y/m/d") ?></h4>
                <div class="d-flex justify-content-evenly mb-4">
                    <div class="fs-4 d-flex flex-column align-items-center">
                        <div>Active users:</div>
                        <div><?php echo count($userActivity); ?></div>
                    </div>
                    <div class="fs-4 d-flex flex-column align-items-center">
                        <div>Total website logins:</div>
                        <div><?php echo $login_count; ?></div>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userActivity as $activity): ?>
                            <tr>
                                <td><?php echo strtoupper($activity['username']); ?></td>
                                <td><?php echo $activity['role']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button id="exportUserActivity" class="btn btn-primary">Export PDF</button>
            </div>

            <!-- Maintenance Schedule Report -->
            <div id="maintenanceScheduleReport" class="report-tab p-3 mt-2">
                <h4>Reports total for: <?php date_default_timezone_set("Asia/Manila");
                                        echo date("Y/m/d") ?></h4>
                <div class="d-flex justify-content-evenly mb-4">
                    <div class="fs-4 d-flex flex-column align-items-center">
                        <div>Total maintenance schedule:</div>
                        <div><?php echo count($maintenanceSchedule); ?></div>
                    </div>
                    <div class="fs-4 d-flex flex-column align-items-center">
                        <div>Successful:</div>
                        <div><?php echo $done; ?></div>
                    </div>
                    <div class="fs-4 d-flex flex-column align-items-center">
                        <div>Cancelled:</div>
                        <div><?php echo $delete; ?></div>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Room Code</th>
                            <th>Maintenance</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maintenanceSchedule as $schedule): ?>
                            <tr>
                                <td><?php echo strtoupper($schedule['room_code']); ?></td>
                                <td><?php echo strtoupper($schedule['label']); ?></td>
                                <td><?php echo $schedule['maintenance_date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button id="exportMaintenanceSchedule" class="btn btn-primary">Export PDF</button>
            </div>
        </div>
    </div>
</main>

<?php
include "closing.php";
?>