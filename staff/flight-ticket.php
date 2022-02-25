<?php
require('validateLogin.php');

// Create connection with MySQL database
$connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$passengerNameInvalid = false;
$passengerEmailInvalid = false;
$passengerDateOfBirthInvalid = false;
$passengerGenderInvalid = false;
$flightScheduleNumberInvalid = false;
$seatNumberInvalid = false;

$passengerName = $_POST['passengerName'] ?? null;
$passengerEmail = $_POST['passengerEmail'] ?? null;
$passengerDateOfBirth = $_POST['passengerDateOfBirth'] ?? null;
$passengerGender = $_POST['passengerGender'] ?? null;
$flightScheduleNumber = $_POST['flightScheduleNumber'] ?? null;
$baggageLimit = $_POST['baggageLimit'] ?? null;
$seatNumber = $_POST['seatNumber'] ?? null;

// Send POST request
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (empty($passengerName)) {
        $passengerNameInvalid = true;
    }

    if (empty($passengerEmail)) {
        $passengerEmailInvalid = true;
    }

    if (empty($passengerDateOfBirth)) {
        $passengerDateOfBirthInvalid = true;
    }

    if (($passengerGender) == "Select") {
        $passengerGenderInvalid = true;
    }

    if ($flightScheduleNumber == "") {
        $flightScheduleNumberInvalid = "This field is required";
    } else {
        $flightScheduleResult = $connection->query("SELECT * FROM flight_schedules WHERE id = " . $flightScheduleNumber);
        if ($flightScheduleResult->num_rows == 0) {
            $flightScheduleNumberInvalid = "Invalid flight number";
        }
    }

    if (empty($seatNumber)) {
        $seatNumberInvalid = "This field is required.";
    } else {
        // Check that the plane has the seat number (regardless of availability)
        $flightInfo = $connection->query("SELECT flights.type, flights.capacity FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$flightScheduleNumber'")->fetch_assoc();

        $flightCapacity = $flightInfo['capacity'];
        $column = substr($seatNumber, -1);
        if (ctype_alpha($column) && ctype_upper($column)) {
            /*
        Seat Arrangement
        International Flights: 2 3 2
        Domestic Flights: 3 3
        */
            $row = substr($seatNumber, 0, -1);
            if ($flightInfo['type'] == "Domestic") {
                if ($column < 'A' || $column > 'F') {
                    $seatNumberInvalid = "Invalid seat.";
                } else {
                    $maxRow = $flightCapacity / 6;
                    if ($row < 1 || $row > $maxRow) {
                        $seatNumberInvalid = "Invalid seat.";
                    }
                }
            } else {
                if ($column < 'A' || $column > 'G') {
                    $seatNumberInvalid = "Invalid seat.";
                } else {
                    $maxRow = $flightCapacity / 7;
                    if ($row < 1 || $row > $maxRow) {
                        $seatNumberInvalid = "Invalid seat.";
                    }
                }
            }
        } else {
            $seatNumberInvalid = "Invalid seat.";
        }

        if (!$seatNumberInvalid) {
            // Check the seat availability
            $taken = $connection->query("SELECT seat_no FROM flight_tickets WHERE schedule_no = '$flightScheduleNumber' AND seat_no = '" . $seatNumber . "'")->num_rows > 0;
            if ($taken) {
                $seatNumberInvalid = "Seat has been taken.";
            }
        }
    }

    if (!$passengerNameInvalid && !$passengerDateOfBirthInvalid && !$passengerGenderInvalid && !$flightScheduleNumberInvalid && !$seatNumberInvalid) {
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

        // Function to identify the class of the seat
        function identifySeatClass($seatNumber)
        {
            /*
        First class - Row 1 to 3 (Total seat in domestic = 3 x 6 = 18, Total seat in international = 3 x 7 = 21)
        Business class - Row 4 to 8 (Total seat in domestic = 5 x 6 = 30, Total seat in international = 5 x 7 = 35)
        Economy class - The rest
        */
            $row = substr($seatNumber, 0, -1);

            if ($row <= 3) {
                return 1; // First - 1
            } else if ($row <= 8) {
                return 2; // Business - 2
            } else {
                return 3; // Economy - 3
            }
        }

        if ($allow) {
            $class = identifySeatClass($seatNumber);
            if (isset($_GET['id'])) {
                // ID is provided, this is a update
                if ($connection->query("UPDATE flight_tickets SET passenger_name='$passengerName', date_of_birth='$passengerDateOfBirth', gender='$passengerGender', schedule_no='$flightScheduleNumber', baggage_limit=$baggageLimit, seat_no='$seatNumber', class=$class WHERE id=" . $_GET['id']) === true) {
                    $successful = true;
                } else {
                    echo $connection->error;
                    $error = true;
                }
            } else {
                // ID is not provided, this is a new flight
                if ($connection->query("INSERT INTO flight_tickets (passenger_name,  date_of_birth, gender, schedule_no, baggage_limit, seat_no, class) VALUES ('$passengerName', '$passengerDateOfBirth', '$passengerGender', '$flightScheduleNumber', $baggageLimit, '$seatNumber', $class)") === true) {
                    // Successfully added the record, redirecting to the record's page
                    header('Location: flight-ticket.php?id=' . $connection->insert_id);
                    $connection->close();
                    exit();
                } else {
                    echo $connection->error;
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
    <title>AirAsia Staff Portal | Flight Ticket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body>
    <?php
    $page = 'ticketSales';
    require('navbar.php');

    if (isset($_GET['id'])) {
        $result = $connection->query("SELECT * FROM flight_tickets WHERE id = '" . $_GET['id'] . "';")->fetch_assoc();
        $passengerName = $passengerName ?? $result['passenger_name'];
        $passengerDateOfBirth = $passengerDateOfBirth ?? $result['date_of_birth'];
        $passengerGender = $passengerGender ?? $result['gender'];
        $flightScheduleNumber = $flightScheduleNumber ?? $result['schedule_no'];
        $baggageLimit = $baggageLimit ?? $result['baggage_limit'];
        $seatNumber = $seatNumber ?? $result['seat_no'];
    }

    $passengerGenders = [
        'Select',
        'Male',
        'Female'
    ];

    $baggageSelections = [
        '0' => 'No checked baggage',
        '20' => '20 kg (50.00 MYR)',
        '25' => '25 kg (60.00 MYR)',
        '30' => '30 kg (110.00 MYR)',
        '40' => '40 kg (150.00 MYR)'
    ];
    ?>
    <div class="container-fluid p-3">
        <h3><?php echo isset($_GET['id']) ? "Update" : "Add" ?> Flight Ticket</h3>
        <?php echo isset($error) ? '<div class="alert alert-danger col-6">An error has occured. Please try again.</div>' : "" ?>
        <?php echo isset($successful) ? '<div class="alert alert-success col-6">Update successful.</div>' : "" ?>
        <form action="flight-ticket.php<?php echo isset($_GET['id']) ? "?id=" . $_GET['id'] : "" ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="passengerName">Passenger Name</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="text" class="form-control <?php if ($passengerNameInvalid) echo "is-invalid"; ?>" placeholder="e.g. John Doe" value="<?php echo $passengerName ?? ""; ?>" id="passengerName" name="passengerName">
                    <?php if ($passengerNameInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
                <div class="form-group col-3">
                    <label for="passengerEmail">Passenger Email</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="email" class="form-control <?php if ($passengerEmailInvalid) echo "is-invalid"; ?>" placeholder="e.g. example@example.com" value="<?php echo $passengerEmail ?? ""; ?>" id="passengerEmail" name="passengerEmail">
                    <?php if ($passengerEmailInvalid) echo '<div class="invalid-feedback">Invalid email.</div>'; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="passengerDateOfBirth">Passenger Date of Birth</label>
                    <div class="input-group date" id="passengerDateOfBirth" data-target-input="nearest">
                        <!-- Show error if invalid and display previous data if there is previous data -->
                        <input type="text" class="form-control datetimepicker-input <?php if ($passengerDateOfBirthInvalid) echo "is-invalid"; ?>" value="<?php echo $passengerDateOfBirth ?? ""; ?>" data-target="#passengerDateOfBirth" id="passengerDateOfBirth" name="passengerDateOfBirth">
                        <div class="input-group-append" data-target="#passengerDateOfBirth" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                        </div>
                        <?php if ($passengerDateOfBirthInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                    </div>
                </div>
                <div class="form-group col-3">
                    <label for="passengerGender">Passenger Gender</label>
                    <select id="gender" class="form-control <?php if ($passengerGenderInvalid) echo "is-invalid"; ?>" name="passengerGender">
                        <?php
                        foreach ($passengerGenders as $text) {
                            echo "<option " . ($passengerGender == $text ? 'selected' : '') . " value='$text'>$text</option>";
                        }
                        ?>
                    </select>
                    <!-- Display previous data if there is previous data -->
                    <?php if ($passengerGenderInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="flightScheduleNumber">Flight Schedule Number (Search <a href="flight-schedule-search.php" target="_blank">here</a>)</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="number" class="form-control <?php if ($flightScheduleNumberInvalid) echo "is-invalid"; ?>" value="<?php echo $flightScheduleNumber ?? ""; ?>" id="flightScheduleNumber" name="flightScheduleNumber">
                    <?php if ($flightScheduleNumberInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
                <div class="form-group col-3">
                    <label for="baggageLimit">Baggage Limit</label>
                    <select id="baggage" class="form-control" name="baggageLimit">
                        <?php
                        foreach ($baggageSelections as $value => $text) {
                            echo "<option " . ($value == 0 ? 'selected' : '') . " value='$value'>$text</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="seatNumber">Seat Number</label><input type="text" class="form-control <?php if ($seatNumberInvalid) echo "is-invalid"; ?>" value="<?php echo $seatNumber ?? ""; ?>" id="seatNumber" name="seatNumber">
                    <?php if ($seatNumberInvalid) echo "<div class='invalid-feedback'>$seatNumberInvalid</div>"; ?>
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
        <?php
        if (isset($showApprovalModal)) {
            echo "$('#approvalModal').modal('show');";
        }
        ?>
    </script>
    <script type="text/javascript">
        $(function() {
            $('#passengerDateOfBirth').datetimepicker({
                format: 'DD-MM-YYYY',
                maxDate: new Date().toISOString().slice(0, 10),
                useCurrent: false
            });
        });
    </script>

</body>

</html>