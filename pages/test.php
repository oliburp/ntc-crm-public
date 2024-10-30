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

// Function to add or update rooms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $room_code = $_POST['room_code'];
    $room_detail = $_POST['room_detail'];

    if ($_POST['action'] === 'add') {
        // Insert new room
        $sql = "INSERT INTO rooms (room_code, room_detail, room_status) VALUES (?, ?, 'available')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $room_code, $room_detail);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        // Update existing room
        $room_id = $_POST['room_id'];
        $sql = "UPDATE rooms SET room_code = ?, room_detail = ? WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $room_code, $room_detail, $room_id);
        $stmt->execute();
    }
}

// Fetch room details for the edit form
$rooms = fetchRooms();
?>

<main>
    <div class="mainRoomManager">
        <div class="container-fluid py-3">
            <div class="row">
                <!-- Left section: Room List -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h3>Classroom Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Room Code</th>
                                        <th>Details</th>
                                        <th>Edit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rooms as $room): ?>
                                        <tr class="align-middle">
                                            <td class="text-center bg-warning"><?php echo htmlspecialchars($room['room_code']); ?></td>
                                            <td><?php echo htmlspecialchars($room['room_detail']); ?></td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                                    <input type="hidden" name="room_code" value="<?php echo $room['room_code']; ?>">
                                                    <input type="hidden" name="room_detail" value="<?php echo $room['room_detail']; ?>">
                                                    <button type="submit" name="action" value="edit" class="btn btn-primary btn-sm">Update</button>
                                                </form>
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
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h3>Room</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="room_code" class="form-label">Room Code</label>
                                    <input type="text" id="room_code" name="room_code" class="form-control" required value="<?php echo isset($_POST['room_code']) ? $_POST['room_code'] : ''; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="room_detail" class="form-label">Room Details</label>
                                    <input type="text" id="room_detail" name="room_detail" class="form-control" required value="<?php echo isset($_POST['room_detail']) ? $_POST['room_detail'] : ''; ?>">
                                </div>

                                <?php if (isset($_POST['action']) && $_POST['action'] === 'edit'): ?>
                                    <input type="hidden" name="room_id" value="<?php echo $_POST['room_id']; ?>">
                                    <button type="submit" name="action" value="edit" class="btn btn-primary">Update Room</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="add" class="btn btn-success">Add Room</button>
                                <?php endif; ?>
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