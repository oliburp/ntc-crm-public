<?php
$pageTitle = "Conflict Resolver";
include "panel.php";

function fetchConflictingRooms()
{
    global $conn;
    $sql = "SELECT 
                r.room_id,
                r.room_code, 
                DATE_FORMAT(m.maintenance_date, '%m/%d/%Y') AS conflict_date,
                CONCAT(
                    UPPER(SUBSTRING(m.label, 1, 1)),
                    LOWER(SUBSTRING(m.label, 2, LENGTH(m.label)))
                ) AS new_label,
                CONCAT(
                    UPPER(SUBSTRING(s.subject, 1, 1)),
                    LOWER(SUBSTRING(s.subject, 2, LENGTH(s.subject)))
                ) AS new_subject,
                s.course_year
            FROM 
                schedules s
            JOIN 
                room_maintenance m ON s.room_id = m.room_id 
            JOIN 
                rooms r ON s.room_id = r.room_id
            WHERE 
                DATE(s.start_time) = m.maintenance_date 
                AND m.maintenance_status = 'PENDING'";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function fetchAvailableRooms($required_gap_hours = 3)
{
    global $conn;

    $sql = "SELECT r.room_id, r.room_code
            FROM rooms r
            LEFT JOIN schedules s ON r.room_id = s.room_id AND DATE(s.start_time) = CURDATE()
            WHERE (
                s.schedule_id IS NULL OR 
                EXISTS (
                    SELECT 1 FROM schedules s1 
                    JOIN schedules s2 ON s1.room_id = s2.room_id 
                    WHERE s1.room_id = r.room_id 
                    AND TIMESTAMPDIFF(HOUR, s1.end_time, s2.start_time) >= ?
                )
            )
            GROUP BY r.room_id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $required_gap_hours);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


function fetchSchedules()
{
    global $conn;
    $sql = "SELECT schedules.*, rooms.room_code 
            FROM schedules 
            LEFT JOIN rooms ON schedules.room_id = rooms.room_id
            WHERE schedules.end_time > NOW()
            GROUP BY schedules.room_id, TIME(schedules.start_time), TIME(schedules.end_time)
            ORDER BY schedules.start_time ASC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$conflictingRooms = fetchConflictingRooms();
$availableRooms = fetchAvailableRooms();
$scheduledRooms = fetchSchedules();
?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent',
                text: 'Your email was sent successfully!',
                confirmButtonText: 'Okay'
            });
        });
    </script>
<?php endif; ?>

<style>
    table {
        background-color: #658594;
    }

    td div,
    th div {
        text-align: center;
        border: 3px solid #000;
        background: #fff;
        padding: 5px;
        border-radius: 10px;
    }

    td,
    tr,
    th {
        border: 0 !important;
        background: none !important;
    }

    tr>td:nth-child(1) {
        width: 40% !important;
    }

    th {
        text-align: center;
        align-content: center;
    }

    .cf-sat {
        background-color: #e5a49e;
    }

    .cf-fri {
        background-color: #ff65c3;
    }

    .cf-thu {
        background-color: #5ce1e6;
    }

    .cf-wed {
        background-color: #ff3131;
    }

    .cf-tue {
        background-color: #ffde59;
    }

    .cf-mon {
        background-color: #05a2ef;
    }

    .cf-sun {
        background-color: #37ed5c;
    }

    .cf-th {
        background-color: #f0d2d0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // JavaScript for the New Email button to clear the form
        document.getElementById('new-email-btn').addEventListener('click', function() {
            document.getElementById('emailForm').reset();
        });

        // Function to validate email format using regex
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Function to get query parameters from the URL
        function getQueryParams() {
            let params = {};
            window.location.search.substr(1).split("&").forEach(function(item) {
                let pair = item.split("=");
                params[pair[0]] = decodeURIComponent(pair[1]);
            });
            return params;
        }

        // Fetch the status from URL parameters and display Swal notification accordingly
        let params = getQueryParams();
        if (params.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Email sent successfully!',
                confirmButtonText: 'Okay'
            });
        } else if (params.status === 'fail') {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: 'Failed to send email.',
                confirmButtonText: 'Retry'
            });
        }

        // Form validation before submitting
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission for validation

            const emailField = document.getElementById('email-input');
            const email = emailField.value.trim();

            // Validate email field
            if (!email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Required',
                    text: 'Please enter an email address.',
                    confirmButtonText: 'Okay'
                });
                return;
            }

            if (!isValidEmail(email)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Email Format',
                    text: 'Please enter a valid email address.',
                    confirmButtonText: 'Okay'
                });
                return;
            }

            // Conflict Room validation
            const conflictRoomField = document.getElementById('conflict-room');
            if (!conflictRoomField || !conflictRoomField.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please select a conflict room.'
                });
                return;
            }

            // Available Rooms validation: check if at least one checkbox is selected
            const availableRoomsCheckboxes = document.querySelectorAll('#available-rooms .form-check-input');
            const isAnyRoomSelected = Array.from(availableRoomsCheckboxes).some(checkbox => checkbox.checked);

            if (!isAnyRoomSelected) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please select at least one available room.'
                });
                return;
            }

            this.submit();
        });
    });
</script>

<main>
    <div class="conflict-fix mainConflict">
        <!-- Top section displaying the conflicts -->
        <div id="conflict-display" class="mb-4 cf-cont">
            <h5>Schedule Conflicts</h5>
            <ul class="list-group">
                <?php foreach ($conflictingRooms as $conflict): ?>
                    <li class="list-group-item">
                        <?php
                        echo "Room " . $conflict['room_code'] . " - " . $conflict['conflict_date'] . ", " . $conflict['new_label'] . " and " . $conflict['new_subject'] . " - " . $conflict['course_year'];
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="row">
            <!-- Left part: Room schedules -->
            <div class="col-md-6">
                <div class="cf-cont">
                    <div class="d-flex w-100 justify-content-center">
                        <h4>Room Schedules</h4>
                    </div>
                    <div id="room-schedule-list" class="table-responsive">
                        <table class="table table-bordered mt-4">
                            <thead class="thead-dark">
                                <tr>
                                    <th>
                                        <div class="text-dark cf-th">TIME</div>
                                    </th>
                                    <th>
                                        <div class="text-dark cf-th">WEEKDAY</div>
                                    </th>
                                    <th>
                                        <div class="text-dark cf-th">ROOM</div>
                                    </th>
                                    <th>
                                        <div class="text-dark cf-th">YEAR AND COURSE</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scheduledRooms as $room) {
                                    $startDateTime = new DateTime($room['start_time']);
                                    $endDateTime = new DateTime($room['end_time']);
                                    $startDay = $startDateTime->format('l');
                                    $startTime = $startDateTime->format('h:i A');
                                    $endTime = $endDateTime->format('h:i A');
                                    $backgroundColor = '';
                                    switch ($startDay) {
                                        case 'Saturday':
                                            $backgroundColor = 'cf-sat';
                                            break;
                                        case 'Friday':
                                            $backgroundColor = 'cf-fri';
                                            break;
                                        case 'Thursday':
                                            $backgroundColor = 'cf-thu';
                                            break;
                                        case 'Wednesday':
                                            $backgroundColor = 'cf-wed';
                                            break;
                                        case 'Tuesday':
                                            $backgroundColor = 'cf-tue';
                                            break;
                                        case 'Monday':
                                            $backgroundColor = 'cf-mon';
                                            break;
                                        case 'Sunday':
                                            $backgroundColor = 'cf-sun';
                                            break;
                                        default:
                                            $backgroundColor = 'bg-light text-dark';
                                            break;
                                    }

                                    echo '<tr>
                                    <td class="text-center"><div>' . $startTime . ' - ' . $endTime . '</div></td>
                                    <td class="text-center"><div class="' . $backgroundColor . '">' . $startDay . '</td>
                                    <td class="text-center"><div>' . htmlspecialchars($room['room_code']) . '</div></td>
                                    <td class="text-center"><div>' . htmlspecialchars($room['course_year']) . '</div></td>
                                </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right part: Email form for resolving conflict -->
            <div class="col-md-6">
                <div class="cf-cont">
                    <div class="d-flex w-100 justify-content-center">
                        <h4>Send Email</h4>
                    </div>
                    <form id="emailForm" method="POST" action="../backend/send-email.php" class="needs-validation" novalidate>
                        <div class="form-group mb-3">
                            <input type="email" id="email-input" name="email" class="form-control" placeholder="Enter email" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>

                        <div class="form-group mb-3">
                            <select id="conflict-room" name="conflict_room" class="form-select" required>
                                <option value="" disabled selected>Select Conflict Room</option>
                                <?php foreach ($conflictingRooms as $room): ?>
                                    <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_code'] . " - " . $room['conflict_date']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a conflicting room.</div>
                        </div>

                        <label for="available-rooms">Available Rooms:</label>
                        <div id="available-rooms" class="mb-3">
                            <?php foreach ($availableRooms as $room): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_rooms[]" value="<?php echo htmlspecialchars($room['room_id']); ?>">
                                    <label class="form-check-label"><?php echo htmlspecialchars($room['room_code']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="invalid-feedback">Please select an available room.</div>

                        <div class="form-group mb-3">
                            <textarea id="additional-message" name="additional_message" class="form-control" placeholder="Enter additional message (optional)"></textarea>
                        </div>

                        <button type="button" id="new-email-btn" class="btn btn-secondary mb-3">New Email</button>
                        <button type="submit" class="btn btn-primary mb-3">Send Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>


<?php
include "closing.php";
?>