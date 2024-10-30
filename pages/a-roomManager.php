<?php
$pageTitle = "Room Manager";
include "panel.php";

// Fetch rooms for the left column display
function fetchRooms()
{
    global $conn;
    $sql = "SELECT * FROM rooms";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to check if a room code already exists
function roomCodeExists($room_code)
{
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

// Function to add or update rooms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $room_code = strtoupper($_POST['room_code']);
    $room_detail = $_POST['room_detail'];

    // Validate room_code format
    if (!preg_match('/^[A-C]/', $room_code)) {
        echo "<script>Swal.fire('Error', 'Room code must start with A, B, or C.', 'error');</script>";
    } elseif (empty($room_detail)) {
        echo "<script>Swal.fire('Error', 'Please select a room detail.', 'error');</script>";
    } elseif ($_POST['action'] === 'add' && roomCodeExists($room_code)) {
        echo "<script>Swal.fire('Error', 'Room code already exists. Please use a different code.', 'error');</script>";
    } else {
        if ($_POST['action'] === 'add') {
            // Insert new room
            $sql = "INSERT INTO rooms (room_code, room_detail, room_status) VALUES (?, ?, 'available')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $room_code, $room_detail);
            $stmt->execute();
            echo "<script>Swal.fire('Success', 'Room added successfully.', 'success').then(() => { window.location = '" . $_SERVER['PHP_SELF'] . "'; });</script>";
        } elseif ($_POST['action'] === 'edit') {
            // Update existing room
            $room_id = $_POST['room_id'];
            $sql = "UPDATE rooms SET room_code = ?, room_detail = ? WHERE room_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $room_code, $room_detail, $room_id);
            $stmt->execute();
            echo "<script>Swal.fire('Success', 'Room updated successfully.', 'success').then(() => { window.location = '" . $_SERVER['PHP_SELF'] . "'; });</script>";
        }
    }
}

// Fetch room details for the edit form
$rooms = fetchRooms();
?>

<style>
    td div,
    td button {
        text-align: center;
        border: 3px solid #000;
        background: #fff;
        padding: 10px;
        border-radius: 20px;
    }

    td,
    tr {
        border: 0 !important;
    }

    tr>td:nth-child(3) {
        width: 30% !important;
    }

    tr>td:nth-child(2)>div {
        background: none;
    }

    tr>td:nth-child(3)>button {
        border: 3px solid #fff;
        background-color: #47C7FE;
        width: 100%;
    }

    th {
        text-align: center;
    }

    .rm-red {
        background-color: #f70d28;
    }

    .rm-blue {
        background-color: #0eb2f9;
    }

    .rm-yellow {
        background-color: #f8e00c;
    }

    .rm-green {
        background-color: #37ed5c;
    }
</style>

<script>
    function populateForm(roomCode, roomDetail, roomId) {
        document.getElementById('room_code').value = roomCode;
        document.getElementById('room_detail').value = roomDetail;
        document.getElementById('room_id').value = roomId;
        document.getElementById('submitBtn').value = "edit";
        document.getElementById('submitBtn').innerText = "Update Room";
        document.getElementById('submitBtn').className = "btn btn-primary";
    }
</script>

<main>
    <div class="mainRoomManager">
        <div class="container-fluid py-3">
            <div class="row">
                <!-- Left section: Room List -->
                <div class="col-md-7">
                    <div class="card rm-card">
                        <div class="card-header bg-info text-white text-center rm-header">
                            <h3>Classroom Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table" border="0">
                                <tbody>
                                    <?php foreach ($rooms as $room): ?>
                                        <?php
                                        // Determine background color based on room_detail
                                        $backgroundColor = '';
                                        switch ($room['room_detail']) {
                                            case 'Television':
                                                $backgroundColor = 'rm-yellow';
                                                break;
                                            case 'Projector':
                                                $backgroundColor = 'rm-blue';
                                                break;
                                            case 'Whiteboard':
                                                $backgroundColor = 'rm-red';
                                                break;
                                            case 'Computer':
                                                $backgroundColor = 'rm-green';
                                                break;
                                            default:
                                                $backgroundColor = 'bg-light text-dark';
                                                break;
                                        }
                                        ?>
                                        <tr class="align-middle">
                                            <td class="text-center">
                                                <div class="p-2 <?php echo $backgroundColor; ?>">
                                                    <?php echo htmlspecialchars($room['room_code']); ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div><strong><?php echo htmlspecialchars($room['room_detail']); ?></strong></div>
                                            </td>
                                            <td class="text-center">
                                                <button class="empt" onclick="populateForm('<?php echo htmlspecialchars($room['room_code']); ?>', '<?php echo htmlspecialchars($room['room_detail']); ?>', '<?php echo $room['room_id']; ?>')">Update</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right section: Room Form -->
                <div class="col-md-5">
                    <div class="card rm-card">
                        <div class="rm-header card-header bg-info text-white text-center ">
                            <h3>Room</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="room_code" class="form-label">Room Code</label>
                                    <input type="text" id="room_code" name="room_code" class="form-control" required value="<?php echo isset($_POST['room_code']) ? $_POST['room_code'] : ''; ?>">
                                    <small class="form-text text-muted">Room code must start with A, B, or C.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="room_detail" class="form-label">Room Details</label>
                                    <select id="room_detail" name="room_detail" class="form-select" required>
                                        <option value="" disabled selected>Select room detail</option>
                                        <option value="Computer">Computer</option>
                                        <option value="Projector">Projector</option>
                                        <option value="Television">Television</option>
                                        <option value="Whiteboard">Whiteboard</option>
                                    </select>
                                </div>

                                <input type="hidden" id="room_id" name="room_id" value="">
                                <button id="submitBtn" type="submit" name="action" value="add" class="btn btn-success">Add Room</button>
                            </form>
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