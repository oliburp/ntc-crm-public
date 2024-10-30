<?php
$pageTitle = "Room Selector";
include "../backend/db_connection.php";

function fetchRooms()
{
    global $conn;
    $sql = "SELECT * FROM rooms";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRoomImage($room_detail)
{
    switch ($room_detail) {
        case 'Television':
            return '../images/tv.png';
        case 'Whiteboard':
            return '../images/wb.png';
        case 'Projector':
            return '../images/projector.png';
        case 'Computer':
            return '../images/pc.png';
        default:
            return '../images/NTC-logo.png';
    }
}

if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];

    if ($filter == 'all') {
        $query = "SELECT rooms.*, 
                   CASE WHEN EXISTS (
                        SELECT 1 FROM schedules 
                        WHERE schedules.room_id = rooms.room_id 
                        AND schedules.start_time <= NOW() 
                        AND schedules.end_time >= NOW()
                    ) 
                    THEN 'occupied' 
                    ELSE 'available' 
                    END AS new_room_status
            FROM rooms";
    } else {
        $query = "SELECT rooms.*, 
                   CASE WHEN EXISTS (
                        SELECT 1 FROM schedules 
                        WHERE schedules.room_id = rooms.room_id 
                        AND schedules.start_time <= NOW() 
                        AND schedules.end_time >= NOW()
                    ) 
                    THEN 'occupied' 
                    ELSE 'available' 
                    END AS new_room_status
            FROM rooms WHERE room_detail = '$filter'";
    }

    $result = $conn->query($query);
    if (!$result) {
        die("Error fetching rooms: " . $conn->error);
    }

    while ($room = $result->fetch_assoc()) {
        $room_id = $room['room_id'];
        $room_code = $room['room_code'];
        $room_detail = $room['room_detail'];
        $room_status = $room['new_room_status'];
        $room_image = getRoomImage($room_detail);
?>
        <div class="col-md-4 mb-4">
            <div class="card room" id="room-<?php echo $room_id; ?>" onclick="selectRoom('<?php echo $room_id; ?>')">
                <img src="<?php echo $room_image; ?>" class="card-img-top" alt="Room Image">
                <div class="card-body text-center p-1 av-stat">
                    <h5 class="card-title m-0"><?php echo $room_code; ?></h5>
                    <?php if ($room_status == 'available') : ?>
                        <i class="fa-solid fa-circle-check fa-2xl av-check" style="color: #48e257;"></i>
                    <?php endif ?>
                </div>
            </div>
        </div>
<?php
    }
    exit;
}

$query = "SELECT rooms.*, 
                   CASE WHEN EXISTS (
                        SELECT 1 FROM schedules 
                        WHERE schedules.room_id = rooms.room_id 
                        AND schedules.start_time <= NOW() 
                        AND schedules.end_time >= NOW()
                    ) 
                    THEN 'occupied' 
                    ELSE 'available' 
                    END AS new_room_status
            FROM rooms";
$result = $conn->query($query);
if (!$result) {
    die("Error fetching rooms: " . $conn->error);
}

include "panel.php";
?>

<script>
    function selectRoom(roomId, roomCode) {
        document.getElementById('schedule-room').textContent = `Room Information - ${roomCode}`;
        // Create a new AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../backend/fetch-all-schedules.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Define what to do when the request completes
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                const schedules = JSON.parse(xhr.responseText);

                // Clear any existing schedule information
                const scheduleContainer = document.getElementById('schedule-info');
                scheduleContainer.innerHTML = '';
                // Display each schedule in the room for today
                if (schedules.length > 0) {
                    schedules.forEach(schedule => {
                        const scheduleItem = document.createElement('p');
                        scheduleItem.innerHTML = `
                        <strong>${schedule.start_time} - ${schedule.end_time}</strong> ${schedule.subject} - ${schedule.course_year}
                    `;
                        scheduleContainer.appendChild(scheduleItem);
                    });
                } else {
                    scheduleContainer.innerHTML = '<p>No schedules for today.</p>';
                }
            }
        };

        // Send the roomId as data to the server
        xhr.send("room_id=" + roomId);
    }


    function filterRooms(roomType) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "t-roomSelector.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                document.querySelector('.room-grid').innerHTML = xhr.responseText;
            }
        };

        xhr.send("filter=" + roomType);
    }
</script>

<main>
    <div class="mainRoomSelector p-4">
        <div class="row">
            <div class="col-lg-8 d-flex flex-column flex-content-center">
                <div class="card w-75 mb-4 p-3 fs-2 av-room">
                    <div>Available Rooms: <i class="fa-solid fa-circle-check fa-lg" style="color: #48e257;"></i></div>
                </div>
                <div class="room-grid">
                    <?php
                    while ($room = $result->fetch_assoc()) {
                        $room_id = $room['room_id'];
                        $room_code = $room['room_code'];
                        $room_detail = $room['room_detail'];
                        $room_status = $room['new_room_status'];
                        $room_image = getRoomImage($room_detail);
                    ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                            <div class="card room" id="room-<?php echo $room_id; ?>" onclick="selectRoom('<?php echo $room_id; ?>', '<?php echo $room_code; ?>')">
                                <img src="<?php echo $room_image; ?>" class="card-img-top" alt="Room Image">
                                <div class="card-body text-center p-1 av-stat">
                                    <h5 class="card-title m-0"><?php echo $room_code; ?></h5>
                                    <?php if ($room_status == 'available') : ?>
                                        <i class="fa-solid fa-circle-check fa-2xl av-check" style="color: #48e257;"></i>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="col-lg-4 col align-self-center">
                <div class="card rs-card">
                    <div class="card-header rs-head text-light text-center fs-2">Room Selector</div>
                    <div class="d-flex flex-column m-3">
                        <button class="border btn btn-light flex-fill m-1 fs-4" onclick="filterRooms('all')">All</button>
                        <div class="d-flex flex-fill pr-1">
                            <button class="border btn btn-light flex-fill m-1 fs-4" onclick="filterRooms('projector')">Projector </button>
                            <div class="d-flex"><i class="fa-solid fa-xl fa-square align-self-center" style="color: #0eb2f9;"></i></div>
                        </div>
                        <div class="d-flex flex-fill pr-1">
                            <button class="border btn btn-light flex-fill m-1 fs-4" onclick="filterRooms('television')">Television </button>
                            <div class="d-flex"><i class="fa-solid fa-xl fa-square align-self-center" style="color: #f8e00c;"></i></div>
                        </div>
                        <div class="d-flex flex-fill pr-1">
                            <button class="border btn btn-light flex-fill m-1 fs-4" onclick="filterRooms('whiteboard')">Whiteboard </button>
                            <div class="d-flex"><i class="fa-solid fa-xl fa-square align-self-center" style="color: #f70d28;"></i></div>
                        </div>
                        <div class="d-flex flex-fill pr-1">
                            <button class="border btn btn-light flex-fill m-1 fs-4" onclick="filterRooms('computer')">Computer </button>
                            <div class="d-flex"><i class="fa-solid fa-xl fa-square align-self-center" style="color: #37ed5c;"></i></div>
                        </div>
                    </div>

                    <div class="card m-3">
                        <div class="card-body">
                            <h5 class="card-title" id='schedule-room'>Room Information </h5>
                            <div id="schedule-info">
                                <!-- Schedule information will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php
include "closing.php";
?>