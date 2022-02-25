<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('head.php') ?>
    <title>Flights</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="container-fluid">
    <?php
    require("header.php");

    $departDateInvalid = false;
    $returnDateInvalid = false;
    $guestNumberInvalid = false;
    $arrivalLocationInvalid = false;

    // Input validation
    if (isset($_GET['departDate'])) {
        // Invalid if it is empty
        if (empty($_GET['departDate'])) {
            $departDateInvalid = true;
        } else if (isset($_GET['returnDate']) && !empty($_GET['returnDate'])) {
            // Return date cannot be before depart date
            if (strtotime($_GET['returnDate']) < strtotime($_GET['departDate'])) {
                $returnDateInvalid = true;
            }
        }
    }

    if (isset($_GET['guestNum'])) {
        if (is_numeric($_GET['guestNum']) && $_GET['guestNum'] < 1) {
            // Guest number needs to be more than 0
            $guestNumberInvalid = true;
            $guestNumberError = "Number of guest needs to be more than 0";
        } else if (empty($_GET['guestNum'])) {
            // Invalid if it is empty
            $guestNumberInvalid = true;
            $guestNumberError = "This field is required";
        }
    }

    if (isset($_GET['departLocation'])) {
        // Arrival location cannot be the same as depart location
        if ($_GET['departLocation'] == $_GET['arrivalLocation']) {
            $arrivalLocationInvalid = true;
        }
    }

    // Perform search operation if all inputs are valid
    if (!$departDateInvalid && !$returnDateInvalid && !$guestNumberInvalid && !$arrivalLocationInvalid && isset($_GET['departDate']) && isset($_GET['guestNum']) && isset($_GET['departLocation'])) {
        $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        $guestNumber = $_GET['guestNum'];
        $departLocation = $_GET['departLocation'];
        $arrivalLocation = $_GET['arrivalLocation'];
        $departDate = date("Y-m-d", strtotime($_GET['departDate']));

        // Select all flight schedules that still have enough seats for the number of guests and have not taken off
        $matchingDepartFlightSchedules = $connection->query("SELECT flight_schedules.*, flights.departure, flights.arrival, flights.capacity, flights.type, (SELECT COUNT(*) FROM flight_tickets WHERE flight_tickets.schedule_no = flight_schedules.id) AS ticketNum FROM flight_schedules INNER JOIN flights ON flight_schedules.flight_no = flights.id WHERE DATE(flight_schedules.depart_dateTime) = '$departDate' AND flights.departure = '$departLocation' AND flights.arrival = '$arrivalLocation' AND (flight_schedules.status = 'Scheduled' OR flight_schedules.status = 'Delayed') HAVING ticketNum + $guestNumber < flights.capacity");

        if ($matchingDepartFlightSchedules->num_rows > 0) {
            while ($flightSchedule = $matchingDepartFlightSchedules->fetch_assoc()) {
                $flightScheduleId = $flightSchedule['id'];
                /*
                Ticket classes ID:
                First - 1
                Business - 2
                Economy - 3
                */
                $ticketNumbers = $connection->query("SELECT SUM(class = 1) as first, SUM(class = 2) as business, SUM(class = 3) as economy FROM flight_tickets WHERE schedule_no = '$flightScheduleId'")->fetch_assoc();

                /*
                International Flights have 7 seats per row
                Domestic Flights have 6 seats per row

                First class - Row 1 to 3 (Total seat in domestic = 3 x 6 = 18, Total seat in international = 3 x 7 = 21)
                Business class - Row 4 to 8 (Total seat in domestic = 5 x 6 = 30, Total seat in international = 5 x 7 = 35)
                Economy class - The rest
                */
                if ($flightSchedule['type'] == 'International') {
                    if ($ticketNumbers['economy'] < $flightSchedule['capacity'] - 21 - 35) {
                        // There are still empty economy seats
                        $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=3")->fetch_assoc()['price'];
                    } else {
                        // Economy is full
                        if ($ticketNumbers['business'] < 35) {
                            // There are still empty business seats
                            $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=2")->fetch_assoc()['price'];
                        } else {
                            // Only first class seats are empty
                            $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=1")->fetch_assoc()['price'];
                        }
                    }
                } else {
                    if ($ticketNumbers['economy'] < $flightSchedule['capacity'] - 18 - 30) {
                        // There are still empty economy seats
                        $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=3")->fetch_assoc()['price'];
                    } else {
                        // Economy is full
                        if ($ticketNumbers['business'] < 30) {
                            // There are still empty business seats
                            $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=2")->fetch_assoc()['price'];
                        } else {
                            // Only first class seats are empty
                            $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=1")->fetch_assoc()['price'];
                        }
                    }
                }

                $duration = (new DateTime($flightSchedule['depart_dateTime']))->diff(new DateTime($flightSchedule['arrive_dateTime']));

                $departFlightSchedules[] = [
                    'id' => $flightScheduleId,
                    'departTime' => date('d-m-Y H:i', strtotime($flightSchedule['depart_dateTime'])),
                    'arriveTime' => date('d-m-Y H:i', strtotime($flightSchedule['arrive_dateTime'])),
                    'duration' => $duration->h > 0 ? $duration->h . 'h ' . $duration->i . 'mins' : $duration->i . 'mins',
                    'price' => number_format($lowestPrice, 2)
                ];
            }
        }

        if (isset($_GET['returnDate']) && !empty($_GET['returnDate'])) {
            $returnDate = date("Y-m-d", strtotime($_GET['returnDate']));

            // Select all flight schedules that still have enough seats for the number of guests
            $matchingReturnFlightSchedules = $connection->query("SELECT flight_schedules.*, flights.departure, flights.arrival, flights.capacity, flights.type, (SELECT COUNT(*) FROM flight_tickets WHERE flight_tickets.schedule_no = flight_schedules.id) AS ticketNum FROM flight_schedules INNER JOIN flights ON flight_schedules.flight_no = flights.id WHERE DATE(flight_schedules.depart_dateTime) = '$returnDate' AND flights.departure = '$arrivalLocation' AND flights.arrival = '$departLocation' HAVING ticketNum + $guestNumber < flights.capacity");

            if ($matchingReturnFlightSchedules->num_rows > 0) {
                while ($flightSchedule = $matchingReturnFlightSchedules->fetch_assoc()) {
                    $flightScheduleId = $flightSchedule['id'];
                    /*
                Ticket classes ID:
                First - 1
                Business - 2
                Economy - 3
                */
                    $ticketNumbers = $connection->query("SELECT SUM(class = 1) as first, SUM(class = 2) as business, SUM(class = 3) as economy FROM flight_tickets WHERE schedule_no = '$flightScheduleId'")->fetch_assoc();

                    /*
                International Flights have 7 seats per row
                Domestic Flights have 6 seats per row

                First class - Row 1 to 3 (Total seat in domestic = 3 x 6 = 18, Total seat in international = 3 x 7 = 21)
                Business class - Row 4 to 8 (Total seat in domestic = 5 x 6 = 30, Total seat in international = 5 x 7 = 35)
                Economy class - The rest
                */
                    if ($flightSchedule['type'] == 'International') {
                        if ($ticketNumbers['economy'] < $flightSchedule['capacity'] - 21 - 35) {
                            // There are still empty economy seats
                            $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=3")->fetch_assoc()['price'];
                        } else {
                            // Economy is full
                            if ($ticketNumbers['business'] < 35) {
                                // There are still empty business seats
                                $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=2")->fetch_assoc()['price'];
                            } else {
                                // Only first class seats are empty
                                $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=1")->fetch_assoc()['price'];
                            }
                        }
                    } else {
                        if ($ticketNumbers['economy'] < $flightSchedule['capacity'] - 18 - 30) {
                            // There are still empty economy seats
                            $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=3")->fetch_assoc()['price'];
                        } else {
                            // Economy is full
                            if ($ticketNumbers['business'] < 30) {
                                // There are still empty business seats
                                $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=2")->fetch_assoc()['price'];
                            } else {
                                // Only first class seats are empty
                                $lowestPrice = $connection->query("SELECT price FROM ticket_class WHERE id=1")->fetch_assoc()['price'];
                            }
                        }
                    }

                    $duration = (new DateTime($flightSchedule['depart_dateTime']))->diff(new DateTime($flightSchedule['arrive_dateTime']));

                    $returnFlightSchedules[] = [
                        'id' => $flightScheduleId,
                        'departTime' => date('d-m-Y H:i', strtotime($flightSchedule['depart_dateTime'])),
                        'arriveTime' => date('d-m-Y H:i', strtotime($flightSchedule['arrive_dateTime'])),
                        'duration' => $duration->h > 0 ? $duration->h . 'h ' . $duration->i . 'mins' : $duration->i . 'mins',
                        'price' => number_format($lowestPrice, 2)
                    ];
                }
            }
        }

        $connection->close();
    }

    // Declare list of airports
    $airports = [
        "Adelaide Airport (YPAD)",
        "Brunei International Airport (WBSB)",
        "Beijing Capital International Airport (ZBAA)",
        "Bintulu Airport (WBGB)",
        "Lien Khuong Airport (VVDL)",
        "Fukuoka Airport (RJFF)",
        "Goa International Airport (VOGO)",
        "Luang Prabang International Airport (VLLB)",
        "Melbourne Airport (YMML)",
        "Kuala Lumpur International Airport (WMKK)",
        "London Gatwick Airport (EGKK)",
        "Hong Kong International Airport (VHHH)",
        "Honolulu International Airport (PHNL)",
        "Iloilo International Airport (RPVI)",
        "Incheon International Airport (RKSI)"
    ];

    ?>
    <form action="flights.php" method="GET">
        <div class="form-row">
            <div class="form-group col-2">
                <label for="departDate">Depart Date</label>
                <div class="input-group date" id="departDatePicker" data-target-input="nearest">
                    <!-- Show error if invalid -->
                    <input type="text" class="form-control datetimepicker-input <?php if ($departDateInvalid) echo "is-invalid"; ?>" value="<?php echo $_GET['departDate'] ?? ""; ?>" data-target="#departDatePicker" id="departDate" name="departDate" />
                    <div class="input-group-append" data-target="#departDatePicker" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                    </div>
                    <?php if ($departDateInvalid) echo '<div class="invalid-feedback">This field is required.</div>'; ?>
                </div>
            </div>
            <div class="form-group col-2">
                <label for="returnDate">Return Date</label>
                <div class="input-group date" id="returnDatePicker" data-target-input="nearest">
                    <!-- Show error if invalid and display previous data if there is previous data -->
                    <input type="text" class="form-control datetimepicker-input <?php if ($returnDateInvalid) echo "is-invalid"; ?>" value="<?php echo $_GET['returnDate'] ?? ""; ?>" data-target="#returnDatePicker" id="returnDate" name="returnDate" />
                    <div class="input-group-append" data-target="#returnDatePicker" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                    </div>
                    <?php if ($returnDateInvalid) echo '<div class="invalid-feedback">Return date cannot be earlier than depart date.</div>'; ?>
                </div>
            </div>
            <div class="form-group col-auto">
                <label for="guestNum">Number of Guest</label>
                <!-- Show error if invalid and display previous data if there is previous data -->
                <input type="number" class="form-control <?php if ($guestNumberInvalid) echo "is-invalid"; ?>" value="<?php echo $_GET['guestNum'] ?? ""; ?>" id="guestNum" name="guestNum">
                <?php if ($guestNumberInvalid) echo '<div class="invalid-feedback">' . $guestNumberError . '</div>'; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-2">
                <label for="departLocation">From</label>
                <select id="departLocation" class="form-control" name="departLocation">
                    <?php
                    foreach ($airports as $airport) {
                        echo "<option " . ((isset($_GET['departLocation']) && $_GET['departLocation'] == $airport) ? "selected" : "") . ">$airport</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-2">
                <label for="arrivalLocation">To</label>
                <select id="arrivalLocation" class="form-control <?php if ($arrivalLocationInvalid) echo "is-invalid"; ?>" name="arrivalLocation">
                    <?php
                    foreach ($airports as $airport) {
                        echo "<option " . ((isset($_GET['arrivalLocation']) && $_GET['arrivalLocation'] == $airport) ? "selected" : "") . ">$airport</option>";
                    }
                    ?>
                </select>
                <?php if ($arrivalLocationInvalid) echo '<div class="invalid-feedback">Destination cannot be the same as the origin.</div>'; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- If the search operation is performed, $connection will be true. Thus, if $connection is false, no search is performed and there is no results to be displayed -->
    <?php if (isset($connection)) { ?>
        <p class="mt-5">Departing Flights</p>
        <?php if (isset($departFlightSchedules)) { ?>
            <table class="table border-bottom border-right border-left table-hover" id="departFlights">
                <?php
                // Display all depart flight schedules available
                foreach ($departFlightSchedules as $flightSchedule) {
                    echo '<tr data-id="' . $flightSchedule['id'] . '">';
                    echo '<td>' . $flightSchedule['departTime'] . '-------------------------------------------' . $flightSchedule['arriveTime'] . '</td>';
                    echo '<td>' . $flightSchedule['duration'] . '</td>';
                    echo '<td>' . $flightSchedule['price'] . ' MYR</td>';
                    echo '</tr>';
                }
                ?>
            </table>
        <?php
            // Display message if there are no available flights
        } else echo "<p>There are no available flights.</p>";
        ?>

        <?php if (isset($_GET['returnDate']) && !empty($_GET['returnDate'])) { ?>
            <p id="returnFlightLabel" class="mt-5">Return Flights</p>
            <?php if (isset($returnFlightSchedules)) { ?>
                <table class="table border-bottom border-right border-left table-hover" id="returnFlights">
                    <?php
                    // Display all return flight schedules available
                    foreach ($returnFlightSchedules as $flightSchedule) {
                        echo '<tr data-id="' . $flightSchedule['id'] . '">';
                        echo '<td>' . $flightSchedule['departTime'] . '-------------------------------------------' . $flightSchedule['arriveTime'] . '</td>';
                        echo '<td>' . $flightSchedule['duration'] . '</td>';
                        echo '<td>' . $flightSchedule['price'] . ' MYR</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
            <?php
                // Display message if there are no available flights
            } else echo "<p>There are no available flights.</p>";
            ?>
        <?php } ?>
    <?php } ?>

    <form action="guestInfo.php" method="POST">
        <input type="hidden" name="departFlight">
        <input type="hidden" name="returnFlight">
        <input type="hidden" name="guestNum" value="<?php echo $_GET['guestNum'] ?? ""; ?>">
        <input type="hidden" value="Next" class="btn btn-primary">
    </form>


    <?php require('scripts.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(function() {
            $('#departDatePicker').datetimepicker({
                format: 'DD-MM-YYYY',
                minDate: new Date().toISOString().slice(0, 10)
            });
            $('#returnDatePicker').datetimepicker({
                format: 'DD-MM-YYYY',
                minDate: new Date().toISOString().slice(0, 10)
            });
        });

        $('#departFlights').on('click', 'tr', function(event) {
            $(this).addClass('table-active').siblings().removeClass('table-active');
            $("input[name='departFlight']").val($(this).attr("data-id"));

            if ($("#returnFlightLabel").length) {
                if ($("input[name='returnFlight']").val()) {
                    $("input[value='Next']").attr('type', 'submit');
                }
            } else {
                $("input[value='Next']").attr('type', 'submit');
            }
        });

        $('#returnFlights').on('click', 'tr', function(event) {
            $(this).addClass('table-active').siblings().removeClass('table-active');
            $("input[name='returnFlight']").val($(this).attr("data-id"));

            if ($("input[name='departFlight']").val()) {
                $("input[value='Next']").attr('type', 'submit');
            }
        });
    </script>
</body>

</html>