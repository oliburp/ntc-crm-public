<?php
error_reporting(0);
$pageTitle = "Maintenance Scheduler";
include "panel.php";

// Fetch rooms for dropdown and maintenance logs
function fetchRooms()
{
    global $conn;
    $sql = "SELECT room_id, room_code FROM rooms";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchMaintenanceSchedules()
{
    global $conn;
    // Update SQL query to fetch only pending maintenance logs
    $sql = "SELECT room_maintenance.*, rooms.room_code 
            FROM room_maintenance 
            JOIN rooms ON room_maintenance.room_id = rooms.room_id 
            WHERE room_maintenance.maintenance_status = 'pending'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchMaintenanceLogs()
{
    global $conn;
    $sql = "SELECT * FROM room_maintenance JOIN rooms ON room_maintenance.room_id = rooms.room_id";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$rooms = fetchRooms();
$maintenanceSchedules = fetchMaintenanceSchedules();
$maintenanceLogs = fetchMaintenanceLogs();
?>
<style>
    #fc-dom-1 {
        color: #000;
    }

    .ongoing-icon {
        color: orange;
        margin-right: 5px;
    }

    .done-icon {
        color: green;
        margin-right: 5px;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 400,
            events: '../backend/fetch-maintenance.php', // Fetch maintenance events
            dateClick: function(info) {
                $('#addMaintenanceModal').modal('show');
                document.getElementById('addMaintenanceForm').onsubmit = function(e) {
                    e.preventDefault();
                    addMaintenance(info.dateStr); // Pass the selected date to the function
                };
            },
            eventContent: function(arg) {
                return {
                    html: arg.event.title // Show the label
                };
            },
            eventDidMount: function(arg) {
                let status = arg.event.extendedProps.maintenance_status;

                if (status === 'PENDING') {
                    arg.el.style.backgroundColor = 'orange'; // Background color for ongoing maintenance
                } else if (status === 'COMPLETED') {
                    arg.el.style.backgroundColor = 'green'; // Background color for completed maintenance
                }

                arg.el.style.color = 'black'; // Change text color if needed
            }
        });

        calendar.render();

        // Function to add maintenance
        function addMaintenance(date) {
            const label = document.getElementById('maintenance-label').value.trim();
            const room_id = document.getElementById('select-room').value;

            // Basic validation for label and room_id
            if (!label) {
                Swal.fire('Error', 'Please provide a maintenance label.', 'error');
                return;
            }

            if (!room_id) {
                Swal.fire('Error', 'Please select a room.', 'error');
                return;
            }

            $.ajax({
                url: '../backend/add-maintenance.php',
                method: 'POST',
                data: {
                    label: label,
                    room_id: room_id,
                    maintenance_date: date,
                    maintenance_status: 'PENDING'
                },
                success: function(response) {
                    const res = JSON.parse(response);

                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Maintenance added successfully',
                            text: 'The maintenance record has been added.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            calendar.refetchEvents(); // Refresh calendar events
                            $('#addMaintenanceModal').modal('hide'); // Close the modal after adding
                            location.reload(); // Optionally reload the page
                        });
                    } else {
                        Swal.fire('Error', res.error || 'Error adding maintenance.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'An unexpected error occurred while adding maintenance.', 'error');
                }
            });
        }


        // Edit functionality
        $('.edit-btn').on('click', function() {
            let maintenanceId = $(this).data('id');
            let label = $(this).data('label');
            let roomId = $(this).data('room');
            let date = $(this).data('date');

            // Populate the edit form with current values
            $('#edit-maintenance-id').val(maintenanceId);
            $('#edit-maintenance-label').val(label);
            $('#edit-select-room').val(roomId);
            $('#edit-maintenance-date').val(date);

            $('#editMaintenanceModal').modal('show'); // Show the edit modal
        });

        $('#editMaintenanceForm').on('submit', function(e) {
            e.preventDefault();

            let maintenanceId = $('#edit-maintenance-id').val();
            let label = $('#edit-maintenance-label').val();
            let room_id = $('#edit-select-room').val();
            let maintenance_date = $('#edit-maintenance-date').val();

            $.ajax({
                url: '../backend/edit-delete-maintenance.php',
                method: 'POST',
                data: {
                    action: 'edit',
                    maintenance_id: maintenanceId,
                    label: label,
                    room_id: room_id,
                    maintenance_date: maintenance_date,
                    maintenance_status: 'pending',
                },
                success: function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Maintenance updated successfully',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                calendar.refetchEvents();
                                $('#editMaintenanceModal').modal('hide'); // Hide modal
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', res.error || 'Error updating maintenance.', 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        Swal.fire('Error', 'Invalid JSON response from the server.', 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    Swal.fire('Error', 'Error with AJAX request: ' + textStatus, 'error');
                }
            });
        });



        $('.done-btn').on('click', function() {
            let maintenanceId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Mark this maintenance as done?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark as done',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../backend/edit-delete-maintenance.php',
                        method: 'POST',
                        data: {
                            action: 'mark_done',
                            maintenance_id: maintenanceId,
                        },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Maintenance marked as done',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    calendar.refetchEvents();
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', res.error || 'Error marking maintenance as done.', 'error');
                            }
                        },
                    });
                }
            });
        });



        $('.delete-btn').on('click', function() {
            let maintenanceId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this deletion!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../backend/edit-delete-maintenance.php',
                        method: 'POST',
                        data: {
                            action: 'delete',
                            maintenance_id: maintenanceId,
                        },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Maintenance deleted',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    calendar.refetchEvents();
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', res.error || 'Error deleting maintenance.', 'error');
                            }
                        },
                    });
                }
            });
        });


        $(document).ready(function() {
            $('#notificationButton').on('click', function() {
                $.ajax({
                    url: '../backend/fetch-maintenance-history.php',
                    method: 'GET',
                    success: function(response) {
                        try {
                            const history = JSON.parse(response); // Parse JSON response

                            // Check for errors in the JSON response
                            if (history.error) {
                                alert(history.error);
                                return;
                            }

                            const historyList = $('#change-history');
                            historyList.empty(); // Clear previous items

                            // Append each history entry to the list
                            history.forEach(item => {
                                historyList.append(`
                                                    <li class="list-group-item">
                                                        <strong>${item.status}</strong> - ${item.label} - ${item.room_code} - ${item.changed_at}
                                                    </li>
                                `);
                            });

                            // Show the modal with the maintenance history
                            $('#notificationModal').modal('show');
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                            console.error('Raw response:', response); // Log raw response for debugging
                            alert('Failed to parse maintenance history.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX request failed:', status, error);
                        alert('Failed to fetch maintenance history.');
                    }
                });
            });
        });


    });
</script>

<main>
    <div class="mainMaintenance">
        <div class="container mt-4">
            <div class="row">
                <!-- Left Side: Maintenance Logs -->

                <div class="col-md-4">
                    <div class="mt-cont">
                        <div class="d-flex align-items-center justify-content-end mt-div">
                            <div class="d-flex w-100 justify-content-center">
                                <h5 class="mb-0 text-center wt-50 justify-self-center">Maintenance Logs</h5>
                            </div>
                            <button id="notificationButton" class="btn bg-light border-0 btn-info mb-0 ms-2"><i class="fa-solid fa-bell"></i></button>
                        </div>
                        <br>

                        <!-- Notification History Modal -->
                        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="notificationModalLabel">Maintenance History</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ul id="change-history" class="list-group">
                                            <!-- History items will be dynamically added here -->
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul id="maintenance-log" class="list-group">
                            <?php foreach ($maintenanceLogs as $log): ?>
                                <li class="list-group-item">
                                    <?php echo $log['label']; ?> (<?php echo $log['room_code']; ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Side: FullCalendar with Legends and Maintenance Schedule List -->
                <div class="col-md-8">
                    <div id="top-right">
                        <div class="mt-cont">
                            <div class="d-flex w-100 justify-content-center">
                                <h5>Maintenance Scheduler</h5>
                            </div>
                            <div id="calendar"></div>
                            <div class="mt-2 d-flex justify-content-evenly">
                                <div>Pending <i class="fa-solid fa-square" style="color: #ffa500;"></i></div>
                                <div>Finished <i class="fa-solid fa-square" style="color: #008000;"></i></div>
                            </div>
                        </div>
                        <div class="mt-cont mt-3">
                            <div class="d-flex w-100 justify-content-center">
                                <h5>Maintenance Schedule</h5>
                            </div>
                            <ul id="maintenance-schedule-list" class="list-group">
                                <?php foreach ($maintenanceSchedules as $log): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $log['label']; ?> (<?php echo $log['room_code']; ?>)
                                        <div>
                                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $log['maintenance_id']; ?>" data-label="<?php echo $log['label']; ?>" data-room="<?php echo $log['room_id']; ?>" data-date="<?php echo $log['maintenance_date']; ?>">Edit</button>
                                            <button class="btn btn-success btn-sm done-btn" data-id="<?php echo $log['maintenance_id']; ?>">Done</button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $log['maintenance_id']; ?>">Delete</button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popup Form for Adding Maintenance -->
            <div class="modal fade" id="addMaintenanceModal" tabindex="-1" aria-labelledby="addMaintenanceModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addMaintenanceModalLabel">Add Maintenance</h5>
                        </div>
                        <div class="modal-body">
                            <form id="addMaintenanceForm">
                                <div>
                                    <label for="maintenance-label">Maintenance Name:</label>
                                    <input type="text" class="form-control" id="maintenance-label" name="label" required>
                                </div>

                                <div>
                                    <label for="select-room">Select Room:</label>
                                    <select id="select-room" class="form-control" name="room_id" required>
                                        <?php foreach ($rooms as $room): ?>
                                            <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_code']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <br>

                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popup Form for Editing Maintenance -->
            <div class="modal fade" id="editMaintenanceModal" tabindex="-1" aria-labelledby="editMaintenanceModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editMaintenanceModalLabel">Edit Maintenance</h5>
                        </div>
                        <div class="modal-body">
                            <form id="editMaintenanceForm">
                                <input type="hidden" id="edit-maintenance-id" name="maintenance_id" value="">
                                <div>
                                    <label for="edit-maintenance-label">Maintenance Name:</label>
                                    <input type="text" class="form-control" id="edit-maintenance-label" name="label" required>
                                </div>

                                <div>
                                    <label for="edit-select-room">Select Room:</label>
                                    <select id="edit-select-room" class="form-control" name="room_id" required>
                                        <?php foreach ($rooms as $room): ?>
                                            <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_code']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="edit-maintenance-date">Maintenance Date:</label>
                                    <input type="date" class="form-control" id="edit-maintenance-date" name="maintenance_date" required>
                                </div>
                                <br>

                                <button type="submit" class="btn btn-primary">Update</button>
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