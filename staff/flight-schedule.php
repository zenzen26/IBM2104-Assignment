<?php
require('validateLogin.php');

// Create connection with MySQL database
$connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$flightNumberInvalid = false;
$departDateTimeInvalid = false;
$arrivalDateTimeInvalid = false;

$flightNumber = $_POST['flightNumber'] ?? null;
$flightStatus = $_POST['flightStatus'] ?? null;
$departDateTime = $_POST['departDateTime'] ?? null;
$arrivalDateTime = $_POST['arrivalDateTime'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($flightNumber == "") {
        $flightNumberInvalid = "This field is required";
    } else {
        $flightResult = $connection->query("SELECT * FROM flights WHERE id = " . $flightNumber);
        if ($flightResult->num_rows == 0) {
            $flightNumberInvalid = "Invalid flight number";
        }
    }

    if (empty($departDateTime)) {
        $departDateTimeInvalid = true;
    }

    if (empty($arrivalDateTime)) {
        if (!$departDateTimeInvalid && !$flightNumberInvalid) {
            // Automatically generate the arrival date and time based on the estimated duration if flight number and depart datetime is set while arrival datetime is empty
            // Estimated duration is in minutes
            $estimatedDuration = $connection->query("SELECT est_duration FROM flights WHERE id = " . $flightNumber)->fetch_assoc()['est_duration'];
            $arrivalDateTime = date('d-m-Y H:i:s', strtotime($departDateTime) + $estimatedDuration * 60);
        }
    } else if (!$departDateTimeInvalid && strtotime($arrivalDateTime) < strtotime($departDateTime)) {
        $arrivalDateTimeInvalid = true;
    }

    if (!$flightNumberInvalid && !$departDateTimeInvalid && !$arrivalDateTimeInvalid) {
        $role = $connection->query("SELECT role FROM staff WHERE id = " . $_SESSION['userId'])->fetch_assoc()['role'];
        $allow = false;

        if ($role == "Superior" || $role == "Manager") {
            // User is supervisor or manager, allow them to perform action
            $allow = true;
        } else {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $result = $connection->query("SELECT * FROM staff WHERE email = '" . $_POST['username'] . "' AND password = '" . $_POST['password'] . "' AND (role = 'Superior' OR role = 'Manager');");

                if ($result->num_rows > 0) {
                    // The username and password is valid, allow them to perform action
                    $allow = true;
                } else {
                    // The username and password is invalid, show error message
                    $showApprovalModal = true;
                    $invalidCredentials = true;
                }
            } else {
                // Request for supervisor or manager approval
                $showApprovalModal = true;
            }
        }

        if ($allow) {
            if (isset($_GET['id'])) {
                // ID is provided, this is a update
                if ($connection->query("UPDATE flight_schedules SET flight_no=$flightNumber, depart_dateTime='" . date("Y-m-d H:i:s", strtotime($departDateTime)) . "', arrive_dateTime='" . date("Y-m-d H:i:s", strtotime($arrivalDateTime)) . "', status='$flightStatus' WHERE id=" . $_GET['id']) === true) {
                    $successful = true;
                } else {
                    $error = true;
                }
            } else {
                // ID is not provided, this is a new flight schedule
                if ($connection->query("INSERT INTO flight_schedules (flight_no, depart_dateTime, arrive_dateTime, status) VALUES ($flightNumber, '" . date("Y-m-d H:i:s", strtotime($departDateTime)) . "', '" . date("Y-m-d H:i:s", strtotime($arrivalDateTime)) . "', '$flightStatus')") === true) {
                    // Successfully added the record, redirecting to the record's page
                    header('Location: flight-schedule.php?id=' . $connection->insert_id);
                    $connection->close();
                    exit();
                } else {
                    $error = true;
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>AirAsia Staff Portal | Flight Schedule</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body>
    <?php
    $page = 'flights';
    require('navbar.php');

    if (isset($_GET['id'])) {
        $result = $connection->query("SELECT * FROM flight_schedules WHERE id = '" . $_GET['id'] . "';")->fetch_assoc();
        $flightNumber = $flightNumber ?? $result['flight_no'];
        $flightStatus = $flightStatus ?? $result['status'];
        $departDateTime = $departDateTime ?? date('d-m-Y H:i:s', strtotime($result['depart_dateTime']));
        $arrivalDateTime = $arrivalDateTime ?? date('d-m-Y H:i:s', strtotime($result['arrive_dateTime']));
    }

    $flightStatuses = [
        "Scheduled",
        "Delayed",
        "Departed",
        "In Air",
        "Expected",
        "Diverted",
        "Recovery",
        "Landed",
        "Arrived",
        "Cancelled"
    ]
    ?>
    <div class="container-fluid p-3">
        <h3><?php echo isset($_GET['id']) ? "Update" : "Add" ?> Flight Schedule</h3>
        <?php echo isset($error) ? '<div class="alert alert-danger col-6">An error has occured. Please try again.</div>' : "" ?>
        <?php echo isset($successful) ? '<div class="alert alert-success col-6">Update successful.</div>' : "" ?>
        <form action="flight-schedule.php<?php echo isset($_GET['id']) ? "?id=" . $_GET['id'] : "" ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="flightNumber">Flight Number (Search for flight number <a href="flight-search.php" target="_blank">here</a>)</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="number" class="form-control <?php if ($flightNumberInvalid) echo "is-invalid"; ?>" value="<?php echo $flightNumber ?? ""; ?>" id="flightNumber" name="flightNumber">
                    <?php if ($flightNumberInvalid) echo "<div class='invalid-feedback'>$flightNumberInvalid</div>"; ?>
                </div>
                <div class="form-group col-3">
                    <label for="flightStatus">Flight Status</label>
                    <select id="flightStatus" class="form-control" name="flightStatus">
                        <?php
                        foreach ($flightStatuses as $status) {
                            echo "<option " . (($flightStatus == $status) ? "selected" : "") . ">$status</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="departDateTime">Departure Date and Time</label>
                    <div class="input-group date" id="departDateTimePicker" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input <?php if ($departDateTimeInvalid) echo "is-invalid"; ?>" value="<?php echo $departDateTime ?? ""; ?>" data-target="#departDateTimePicker" id="departDateTime" name="departDateTime" />
                        <div class="input-group-append" data-target="#departDateTimePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                        </div>
                        <?php if ($departDateTimeInvalid) echo '<div class="invalid-feedback">This field is required.</div>'; ?>
                    </div>
                </div>
                <div class="form-group col-3">
                    <label for="arrivalDateTime">Arrival Date and Time</label>
                    <div class="input-group date" id="arrivalDateTimePicker" data-target-input="nearest">
                        <!-- Display previous data if there is previous data -->
                        <input type="text" class="form-control datetimepicker-input <?php if ($arrivalDateTimeInvalid) echo "is-invalid"; ?>" value="<?php echo $arrivalDateTime ?? ""; ?>" data-target="#arrivalDateTimePicker" id="arrivalDateTime" name="arrivalDateTime" />
                        <div class="input-group-append" data-target="#arrivalDateTimePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                        </div>
                        <?php if ($arrivalDateTimeInvalid) echo "<div class='invalid-feedback'>Arrival date and time cannot be before departure date and time.</div>"; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <?php if (isset($showApprovalModal)) { ?>
                <div class="modal" id="approvalModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Supervisor or Manager Approval Required</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php echo isset($invalidCredentials) ? '<div class="alert alert-danger">Invalid username or password</div>' : "" ?>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" placeholder="Username" name="username">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" placeholder="Password" name="password">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
            $connection->close();
            ?>
        </form>
    </div>

    <?php require('../scripts.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>
    <script type="text/javascript">
        // Script to fix time icon not appearing when using FontAwesome 5
        $.fn.datetimepicker.Constructor.Default = $.extend({}, $.fn.datetimepicker.Constructor.Default, {
            icons: {
                time: 'fas fa-clock',
                date: 'fas fa-calendar',
                up: 'fas fa-arrow-up',
                down: 'fas fa-arrow-down',
                previous: 'far fa-chevron-left',
                next: 'far fa-chevron-right',
                today: 'far fa-calendar-check-o',
                clear: 'far fa-trash',
                close: 'far fa-times'
            }
        });

        $(function() {
            $('#departDateTimePicker').datetimepicker({
                format: 'DD-MM-YYYY HH:mm:ss'
            });
            $('#arrivalDateTimePicker').datetimepicker({
                format: 'DD-MM-YYYY HH:mm:ss'
            });
        });

        <?php
        if (isset($showApprovalModal)) {
            echo "$('#approvalModal').modal('show');";
        }
        ?>
    </script>
</body>

</html>