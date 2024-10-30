<?php
$pageTitle = "View Room Schedule";
include "panel.php";

// Function to get rooms by building
function getRoomsByBuilding($conn, $building)
{
    $sql = "SELECT r.*, 
                   (SELECT s.subject 
                    FROM schedules s 
                    WHERE s.room_id = r.room_id 
                    AND s.start_time >= NOW() 
                    ORDER BY s.start_time ASC 
                    LIMIT 1) AS upcoming_subject, 
                   (SELECT s.start_time 
                    FROM schedules s 
                    WHERE s.room_id = r.room_id 
                    AND s.start_time >= NOW() 
                    ORDER BY s.start_time ASC 
                    LIMIT 1) AS upcoming_start_time,
                   (SELECT s.end_time 
                    FROM schedules s 
                    WHERE s.room_id = r.room_id 
                    AND s.start_time >= NOW() 
                    ORDER BY s.start_time ASC 
                    LIMIT 1) AS upcoming_end_time,
                   (SELECT s.course_year 
                    FROM schedules s 
                    WHERE s.room_id = r.room_id 
                    AND s.start_time >= NOW() 
                    ORDER BY s.start_time ASC 
                    LIMIT 1) AS upcoming_course_year
            FROM rooms r 
            WHERE r.room_code LIKE ? 
            ORDER BY r.room_code";

    $stmt = $conn->prepare($sql);
    $param = $building . '%';  // For building A, B, or C
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result;
}

?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const scheduleModal = document.getElementById('scheduleModal');
    let weekOffset = 0;
    let roomId = null;  // Define a global variable to store room ID

    function loadSchedules(roomId) {
        fetch(`../backend/get-schedules.php?room_id=${roomId}&weekOffset=${weekOffset}`)
            .then(response => response.json())
            .then(data => {
                const modalBody = document.getElementById('scheduleModalBody');
                modalBody.innerHTML = ''; // Clear previous schedules

                if (data.length > 0) {
                    data.forEach(schedule => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${schedule.subject}</td>
                            <td>${schedule.start_time}</td>
                            <td>${schedule.end_time}</td>
                            <td>${schedule.course_year}</td>
                        `;
                        modalBody.appendChild(row);
                    });
                } else {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="4">No schedules for this week.</td>`;
                    modalBody.appendChild(row);
                }
            })
            .catch(error => console.error('Error fetching schedules:', error));
    }

    // When the modal is shown, set the room ID and load the initial schedules
    scheduleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;  // Button that triggered the modal
        roomId = button.getAttribute('data-room-id');  // Set the room ID
        weekOffset = 0;  // Reset week offset whenever modal is opened
        loadSchedules(roomId);  // Load schedules for the initial week
    });

    // Previous and Next week navigation
    document.getElementById('prev-week').addEventListener('click', function() {
        weekOffset--;
        loadSchedules(roomId);  // Use the stored roomId
    });

    document.getElementById('next-week').addEventListener('click', function() {
        weekOffset++;
        loadSchedules(roomId);  // Use the stored roomId
    });
});
</script>

<main>
    <div class="mainRoomSchedule">
        <div class="buildingTabs">
            <div class="m-4 w-100">
                <ul class="nav nav-tabs" id="buildingTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active fs-3 text-dark" id="building-a-tab" data-toggle="tab" href="#building-a" role="tab" aria-controls="building-a" aria-selected="true">Building A</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-3 text-dark" id="building-b-tab" data-toggle="tab" href="#building-b" role="tab" aria-controls="building-b" aria-selected="false">Building B</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-3 text-dark" id="building-c-tab" data-toggle="tab" href="#building-c" role="tab" aria-controls="building-c" aria-selected="false">Building C</a>
                    </li>
                </ul>

                <div class="tab-content" id="buildingTabsContent">
                    <!-- Building A -->
                    <div class="tab-pane fade show active" id="building-a" role="tabpanel" aria-labelledby="building-a-tab">
                        <div class="row mt-3">
                            <?php
                            // Fetch rooms for Building A
                            $roomsA = getRoomsByBuilding($conn, 'A');
                            foreach ($roomsA as $room) {
                                echo '<div class="col-md-4">';
                                echo '<div class="card mb-4" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-room-id="' . $room['room_id'] . '">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . $room['room_code'] . '</h5>';

                                // Check if there is an upcoming schedule
                                if ($room['upcoming_subject']) {
                                    $upcomingStartDateTime = new DateTime($room['upcoming_start_time']);
                                    $upcomingEndDateTime = new DateTime($room['upcoming_end_time']);

                                    echo '<p class="schedule-info">' . $room['upcoming_course_year'] . ' <br> ' . $upcomingStartDateTime->format('h:i A') . ' - ' . $upcomingEndDateTime->format('h:i A') . ' </p>';
                                } else {
                                    echo '<p class="schedule-info">No upcoming schedules.</p>';
                                }

                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Building B -->
                    <div class="tab-pane fade" id="building-b" role="tabpanel" aria-labelledby="building-b-tab">
                        <div class="row mt-3">
                            <?php
                            $roomsB = getRoomsByBuilding($conn, 'B');
                            foreach ($roomsB as $room) {
                                echo '<div class="col-md-4">';
                                echo '<div class="card mb-4" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-room-id="' . $room['room_id'] . '">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . $room['room_code'] . '</h5>';

                                // Check if there is an upcoming schedule
                                if ($room['upcoming_subject']) {
                                    $upcomingStartDateTime = new DateTime($room['upcoming_start_time']);
                                    $upcomingEndDateTime = new DateTime($room['upcoming_end_time']);

                                    echo '<p class="schedule-info">' . $room['upcoming_course_year'] . ' <br> ' . $upcomingStartDateTime->format('h:i A') . ' - ' . $upcomingEndDateTime->format('h:i A') . ' </p>';
                                } else {
                                    echo '<p class="schedule-info">No upcoming schedules.</p>';
                                }

                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Building C -->
                    <div class="tab-pane fade" id="building-c" role="tabpanel" aria-labelledby="building-c-tab">
                        <div class="row mt-3">
                            <?php
                            $roomsC = getRoomsByBuilding($conn, 'C');
                            foreach ($roomsC as $room) {
                                echo '<div class="col-md-4">';
                                echo '<div class="card mb-4" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-room-id="' . $room['room_id'] . '">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . $room['room_code'] . '</h5>';

                                // Check if there is an upcoming schedule
                                if ($room['upcoming_subject']) {
                                    $upcomingStartDateTime = new DateTime($room['upcoming_start_time']);
                                    $upcomingEndDateTime = new DateTime($room['upcoming_end_time']);

                                    echo '<p class="schedule-info">' . $room['upcoming_course_year'] . ' <br> ' . $upcomingStartDateTime->format('h:i A') . ' - ' . $upcomingEndDateTime->format('h:i A') . ' </p>';
                                } else {
                                    echo '<p class="schedule-info">No upcoming schedules.</p>';
                                }

                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">Room Schedules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <button id="prev-week" class="btn btn-secondary">Previous Week</button>
                    <button id="next-week" class="btn btn-secondary">Next Week</button>
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Course Year</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleModalBody">
                            <!-- Schedules will be populated here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<?php
include "closing.php";
?>