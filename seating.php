<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('head.php') ?>
    <title>Seat Selection</title>
</head>

<body class="container-fluid">
    <?php
    session_start();
    require("header.php");

    // Create connection with MySQL database
    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    if (isset($_GET['flight']) && $_GET['flight'] == 'return') {
        $flightScheduleId = $_SESSION['returnFlight'];
    } else {
        $flightScheduleId = $_SESSION['departFlight'];
    }

    $takenSeats = [];
    // Get all seats that have been taken
    $takenSeatsResults = $connection->query("SELECT seat_no FROM flight_tickets WHERE schedule_no = '$flightScheduleId'");
    if ($takenSeatsResults->num_rows > 0) {
        while ($seat = $takenSeatsResults->fetch_assoc()) {
            // Populate $takenSeats with the seat numbers that are taken
            $takenSeats[] = $seat['seat_no'];
        }
    }

    // Get prices of tickets
    $ticketPricingResults = $connection->query("SELECT * FROM ticket_class");
    /*
    Ticket classes ID:
    First - 1
    Business - 2
    Economy - 3
    */
    while ($ticketClass = $ticketPricingResults->fetch_assoc()) {
        $ticketPricings[$ticketClass['id']] = $ticketClass['price'];
    }

    $flightInfo = $connection->query("SELECT flights.type, flights.capacity FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$flightScheduleId'")->fetch_assoc();

    $guest = $_GET['guest'] ?? 1;

    if (isset($_GET['seat'])) {
        if (isset($_GET['flight']) && $_GET['flight'] == 'return') {
            $_SESSION['guests'][$guest]['returnFlight']['seat'] = $_GET['seat'];
        } else {
            $_SESSION['guests'][$guest]['departFlight']['seat'] = $_GET['seat'];
        }
    }
    ?>
    <h3 class="form-inline"><?php echo isset($_GET['flight']) && $_GET['flight'] == 'return' ? "Return" : "Departing" ?> Flight Seating for
        <select class="form-control ml-3" onchange="window.location = 'seating.php?flight=<?php echo $_GET['flight'] ?? 'depart'; ?>&guest=' + this.value;">
            <?php
            for ($i = 1; $i <= count($_SESSION['guests']); $i++) {
                echo "<option " . ($i == $guest ? "selected" : "") . " value=" . $i . ">" . $_SESSION['guests'][$i]['name'] . "</option>";
            }
            ?>
        </select>
    </h3>

    <div class="row">
        <table class="border-top border-bottom border-dark m-5">
            <?php
            /*
        Seat Arrangement
        International Flights: 2 3 2
        Domestic Flights: 3 3
        */

            $flightCapacity = $flightInfo['capacity'];
            if ($flightInfo['type'] == 'Domestic') {
                $numOfRow = $flightCapacity / 6;

                for ($i = 1; $i <= $numOfRow; $i++) {
                    echo "<tr>";
                    $letter = 'A';
                    for ($j = 0; $j < 7; $j++) {
                        if ($j == 3) {
                            echo '<td class="pl-4 pr-4"></td>';
                        } else {
                            if (in_array($i . $letter, $takenSeats)) {
                                $colourClass = 'table-secondary';
                            } else if ($i <= 3) {
                                $colourClass = 'table-info';
                            } else if ($i <= 8) {
                                $colourClass = 'table-warning';
                            } else {
                                $colourClass = 'table-success';
                            }

                            if (isset($_GET['flight']) && $_GET['flight'] == 'return') {
                                if (isset($_SESSION['guests'][$guest]['returnFlight']['seat']) && $_SESSION['guests'][$guest]['returnFlight']['seat'] == $i . $letter) {
                                    $colourClass = 'table-primary';
                                }
                            } else if (isset($_SESSION['guests'][$guest]['departFlight']['seat']) && $_SESSION['guests'][$guest]['departFlight']['seat'] == $i . $letter) {
                                $colourClass = 'table-primary';
                            }


                            echo "<td class='$colourClass text-center p-2 border border-dark'>" . (in_array($i . $letter, $takenSeats) ? $i . $letter : "<a href='seating.php?flight=" . ($_GET['flight'] ?? "depart") . "&guest=$guest&seat=$i$letter'>$i$letter</a>") . "</td>";
                            $letter++;
                        }
                    }
                    echo "</tr>";
                }
            } else {
                $numOfRow = $flightCapacity / 7;

                for ($i = 1; $i <= $numOfRow; $i++) {
                    echo "<tr>";
                    $letter = 'A';
                    for ($j = 0; $j < 9; $j++) {
                        if ($j == 2 || $j == 6) {
                            echo '<td class="pl-4 pr-4"></td>';
                        } else {
                            if (in_array($i . $letter, $takenSeats)) {
                                $colourClass = 'table-secondary';
                            } else if ($i <= 3) {
                                $colourClass = 'table-info';
                            } else if ($i <= 8) {
                                $colourClass = 'table-warning';
                            } else {
                                $colourClass = 'table-success';
                            }

                            if (isset($_GET['flight']) && $_GET['flight'] == 'return') {
                                if (isset($_SESSION['guests'][$guest]['returnFlight']['seat']) && $_SESSION['guests'][$guest]['returnFlight']['seat'] == $i . $letter) {
                                    $colourClass = 'table-primary';
                                }
                            } else if (isset($_SESSION['guests'][$guest]['departFlight']['seat']) && $_SESSION['guests'][$guest]['departFlight']['seat'] == $i . $letter) {
                                $colourClass = 'table-primary';
                            }

                            echo "<td class='$colourClass text-center p-2 border border-dark'>" . (in_array($i . $letter, $takenSeats) ? $i . $letter : "<a href='seating.php?flight=" . ($_GET['flight'] ?? "depart") . "&guest=$guest&seat=$i$letter'>$i$letter</a>") . "</td>";
                            $letter++;
                        }
                    }
                    echo "</tr>";
                }
            }


            ?>
        </table>
        <table class="mt-5 ml-3 border border-dark">
            <tr>
                <td class="table-info p-3 border border-dark"></td>
                <td class="p-1 border border-dark">First Class (<?php echo $ticketPricings[1] ?> MYR)</td>
            </tr>
            <tr>
                <td class="table-warning p-3 border border-dark"></td>
                <td class="p-1 border border-dark">Business Class (<?php echo $ticketPricings[2] ?> MYR)</td>
            </tr>
            <tr>
                <td class="table-success p-3 border border-dark"></td>
                <td class="p-1 border border-dark">Economy Class (<?php echo $ticketPricings[3] ?> MYR)</td>
            </tr>
        </table>
        <table class="mt-5 ml-3 border border-dark">
            <tr>
                <td class="table-primary p-3 border border-dark"></td>
                <td class="p-1 border border-dark">Selected Seat</td>
            </tr>
            <tr>
                <td class="table-secondary p-3 border border-dark"></td>
                <td class="p-1 border border-dark">Not Available</td>
            </tr>
        </table>
    </div>
    <div class="row mb-5 ml-4">
        <?php
        $allSeatsSelected = true;
        foreach ($_SESSION['guests'] as $guestDetails) {
            if (isset($_GET['flight']) && $_GET['flight'] == 'return') {
                if (!isset($guestDetails['returnFlight']['seat'])) {
                    $allSeatsSelected = false;
                }
            } else if (!isset($guestDetails['departFlight']['seat'])) {
                $allSeatsSelected = false;
            }
        }

        if ($allSeatsSelected) {
            if (isset($_GET['flight']) && $_GET['flight'] == 'return') {
                echo "<a href='baggage.php'><button type='button' class='btn btn-primary'>Next</button></a>";
            } else if (isset($_SESSION['returnFlight'])) {
                echo "<a href='seating.php?flight=return'><button type='button' class='btn btn-primary'>Next</button></a>";
            } else {
                echo "<a href='baggage.php'><button type='button' class='btn btn-primary'>Next</button></a>";
            }
        }

        $connection->close();
        ?>
    </div>

    <?php require('scripts.php') ?>
</body>

</html>