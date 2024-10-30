<?php
$pageTitle = "Schedule Manager";
include "panel.php";

function fetchRooms()
{
    global $conn;
    $sql = "SELECT * FROM rooms";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$rooms = fetchRooms();
?>
<style>
    .fc .fc-toolbar.fc-header-toolbar {
        margin-bottom: 10px;
    }

    .fc-header-toolbar {
        color: #fff;
    }

    .fc .fc-button-primary {
        background-color: #05a2ef;
    }

    .fc-button:hover {
        background-color: #45a049;
        /* Darker color on hover */
    }

    /* Change the text in the header */
    .fc-toolbar-title {
        font-size: 1.5rem;
        /* Larger font size */
        font-weight: bold;
    }

    .fc-daygrid-day-frame {
        height: auto;
        width: auto;
    }

    .fc-daygrid-day {
        background: #fff;
    }

    .fc-day-today {
        background: rgba(9, 160, 239, 0.4) !important;
    }

    .fc-daygrid-day-top {
        height: 100%;
    }

    .fc-daygrid-day-number {
        width: 100%;
        font-size: large;
        text-align: center;
    }

    .fc-daygrid-day-events {
        display: none;
    }

    #small-calendar>div.fc-header-toolbar.fc-toolbar>div:nth-child(3)>button {
        display: none;
    }

    .fc-daygrid-day {
        margin: 2px;
    }
</style>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var smallCalendarEl = document.getElementById('small-calendar');
        var bigCalendarEl = document.getElementById('big-calendar');
        var addEventButton = document.getElementById('add-event-button');
        var addEventModal = document.getElementById('addEventModal');
        var startTimeInput = document.getElementById('start-time');
        var endTimeInput = document.getElementById('end-time');

        // Initialize the small dayGridMonth calendar
        var smallCalendar = new FullCalendar.Calendar(smallCalendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            height: 'auto',
            events: '../backend/fetch-events.php',
            dateClick: function(info) {
                // Sync small calendar with the big one
                bigCalendar.changeView('timeGridDay', info.dateStr);

                // Set the start and end time in the modal when a date is clicked
                setModalDateTime(info.date);
            },
            timeZone: 'UTC+8'
        });

        // Initialize the big timeGridDay calendar
        var bigCalendar = new FullCalendar.Calendar(bigCalendarEl, {
            initialView: 'timeGridDay',
            slotMinTime: '06:00:00',
            slotMaxTime: '20:00:00',
            expandRows: true,
            editable: true,
            selectable: true,
            events: '../backend/fetch-events.php',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridDay,timeGridWeek'
            },
            allDaySlot: false,
            eventClick: function(info) {
                $('#editEventModal').modal('show');

                document.getElementById('event-id').value = info.event.id;
                document.getElementById('edit-subject').value = info.event.title;
                document.getElementById('edit-course-year').value = info.event.extendedProps.course_year;
                document.getElementById('edit-start-time').value = new Date(info.event.start).toISOString().slice(0, 16);
                document.getElementById('edit-end-time').value = info.event.end ? new Date(info.event.end).toISOString().slice(0, 16) : new Date(info.event.start).toISOString().slice(0, 16);

                let roomSelect = document.getElementById('edit-room');
                let roomCode = info.event.extendedProps.room_code;

                for (let i = 0; i < roomSelect.options.length; i++) {
                    if (roomSelect.options[i].textContent == roomCode) {
                        roomSelect.options[i].selected = true;
                        break;
                    }
                }
            },
            dateClick: function(info) {
                setModalDateTime(info.date);
            },
            eventContent: function(arg) {
                let timeText = arg.timeText;
                return {
                    html: `<b>${arg.event.title}</b><br>${arg.event.extendedProps.room_code} - ${arg.event.extendedProps.course_year} ~ <i>${timeText}</i>`
                };
            },
            timeZone: 'UTC+8'
        });

        // Update event functionality
        document.getElementById('save-event').addEventListener('click', function() {
            const eventId = document.getElementById('event-id').value;
            const subject = document.getElementById('edit-subject').value;
            const room_id = document.getElementById('edit-room').value;
            const start_time = document.getElementById('edit-start-time').value;
            const end_time = document.getElementById('edit-end-time').value;
            const course_year = document.getElementById('edit-course-year').value; // Include course_year

            $.ajax({
                url: '../backend/update-event.php',
                method: 'POST',
                data: {
                    id: eventId,
                    subject: subject,
                    room_id: room_id,
                    start_time: start_time,
                    end_time: end_time,
                    course_year: course_year // Send course_year
                },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Event Updated',
                            text: res.success,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: res.error,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: "Error: " + error,
                        confirmButtonText: 'OK'
                    });
                }
            });

            console.log({
                id: eventId,
                subject: subject,
                room_id: room_id,
                start_time: start_time,
                end_time: end_time,
                course_year: course_year // Log course_year
            });
        });

        // Handle deleting the event
        document.getElementById('delete-event').addEventListener('click', function() {
            const eventId = document.getElementById('event-id').value;

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../backend/delete-event.php',
                        method: 'POST',
                        data: {
                            id: eventId
                        },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Event deleted successfully!',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload(); // Reload the page after deleting the event
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: res.message,
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: "Error: " + error,
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });

        // Function to show the modal and set the date/time inputs
        document.getElementById('add-event-button').addEventListener('click', function() {
            var form = document.getElementById('addEventForm');
            // Toggle the display of the form
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        });

        document.getElementById('addEventForm').onsubmit = function(e) {
            e.preventDefault();
            addEventToDatabase();
        };

        // Function to add event to database
        function addEventToDatabase() {
            const subject = document.getElementById('event-title').value.trim();
            const room_id = document.getElementById('select-room').value;
            const start_time = document.getElementById('start-time').value;
            const end_time = document.getElementById('end-time').value;
            const course_year = document.getElementById('course-year').value;
            const user_id = document.getElementById('user-id').value;
            const repeat_weekly = document.getElementById('repeat-weekly').checked ? 1 : 0; // True by default

            // Validate inputs
            if (!subject || !room_id || !start_time || !end_time || !course_year || !user_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                });
                return;
            }

            if (new Date(start_time) >= new Date(end_time)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'End time must be after start time.',
                });
                return;
            }

            $.ajax({
                url: '../backend/add-event.php',
                method: 'POST',
                data: {
                    subject: subject,
                    room_id: room_id,
                    start_time: start_time,
                    end_time: end_time,
                    course_year: course_year,
                    user_id: user_id,
                    repeat_weekly: repeat_weekly
                },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.success,
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Reload the page to reflect the new event
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.error,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: "An error occurred: " + error,
                    });
                }
            });
        }

        // Helper function to set the modal date and time fields
        function setModalDateTime(selectedDate) {
            // Get the local time and format it to YYYY-MM-DDTHH:MM (for datetime-local input)
            var localDateStr = selectedDate.toISOString().slice(0, 16);

            var endDate = new Date(selectedDate);
            endDate.setHours(selectedDate.getHours() + 1);

            var endDateStr = endDate.toISOString().slice(0, 16);

            startTimeInput.value = localDateStr;
            endTimeInput.value = endDateStr;
        }


        smallCalendar.render();
        bigCalendar.render();
    });
</script>

<main>

    <div class="mainScheduleManager">
        <div class="left-schedule">
            <div id="small-calendar"></div>
            <button class="btn btn-primary" id="add-event-button">Create</button>

            <div id="addEventForm" style="display: none; margin-top: 10px;">
                <form>
                    <div class="mb-3">
                        <label for="event-title" class="form-label">Subject:</label>
                        <input type="text" class="form-control" id="event-title" name="subject" required>
                    </div>

                    <div class="mb-3">
                        <label for="select-room" class="form-label">Room:</label>
                        <select id="select-room" class="form-select" name="room_id" required>
                            <option value="" disabled selected>Choose a room</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room['room_id']); ?>"><?php echo htmlspecialchars($room['room_code']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Hidden input for user_id -->
                    <input type="hidden" id="user-id" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                    <div class="mb-3">
                        <label for="course-year" class="form-label">Course Year:</label>
                        <input type="text" class="form-control" id="course-year" name="course_year" required>
                    </div>

                    <div class="mb-3">
                        <label for="start-time" class="form-label">Start Time:</label>
                        <input type="datetime-local" class="form-control" id="start-time" name="start_time" required>
                    </div>

                    <div class="mb-3">
                        <label for="end-time" class="form-label">End Time:</label>
                        <input type="datetime-local" class="form-control" id="end-time" name="end_time" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="repeat-weekly" name="repeat_weekly" checked>
                            <label class="form-check-label" for="repeat-weekly">Repeat Weekly</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Event</button>
                </form>
            </div>
        </div>
        <div id="big-calendar"></div>
        <!-- Edit Event Modal -->
        <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editEventForm">
                            <input type="hidden" id="event-id" name="id">

                            <label for="edit-subject">Subject:</label>
                            <input type="text" id="edit-subject" name="subject" class="form-control" required>

                            <label for="edit-room">Room:</label>
                            <select id="edit-room" name="room_id" class="form-control">
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_code']; ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label for="edit-course-year">Course Year:</label>
                            <input type="text" id="edit-course-year" name="course_year" class="form-control" required>

                            <label for="edit-start-time">Start Time:</label>
                            <input type="datetime-local" id="edit-start-time" name="start_time" class="form-control" required>

                            <label for="edit-end-time">End Time:</label>
                            <input type="datetime-local" id="edit-end-time" name="end_time" class="form-control" required>
                            <br>
                            <div><label for="edit-repeat-weekly">Repeat Weekly</label>
                                <input type="checkbox" id="edit-repeat-weekly" name="edit_repeat_weekly">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="delete-event">Delete</button>
                        <button type="button" class="btn btn-primary" id="save-event">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php
include "closing.php";
?>