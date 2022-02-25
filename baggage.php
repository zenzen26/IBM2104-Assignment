<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('head.php') ?>
    <title>Baggage Selection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="container-fluid">
    <?php
    require("header.php");

    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $departFlightScheduleId = $_SESSION['departFlight'];
    $departFlightInfo = $connection->query("SELECT flights.registration FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$departFlightScheduleId'")->fetch_assoc();

    if (isset($_SESSION['returnFlight'])) {
        $returnFlightScheduleId = $_SESSION['returnFlight'];
        $returnFlightInfo = $connection->query("SELECT flights.registration FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$returnFlightScheduleId'")->fetch_assoc();
    }

    ?>

    <form action="confirmation.php" method="POST">
        <?php

        $baggageSelections = [
            '0' => 'No checked baggage',
            '20' => '20 kg (50.00 MYR)',
            '25' => '25 kg (60.00 MYR)',
            '30' => '30 kg (110.00 MYR)',
            '40' => '40 kg (150.00 MYR)',
        ];

        for ($i = 1; $i <= count($_SESSION['guests']); $i++) {
            $guestDetail = $_SESSION['guests'][$i];
            echo "<h5>Guest $i: " . $guestDetail['name'] . "</h5>";
        ?>
            <div class="row mb-5">
                <div class="col-2">
                    <h6 class="mt-2">Departing Flight (<?php echo $departFlightInfo['registration']; ?>)</h6>
                    <div class="form-group">
                        <label for="baggage">Baggage</label>
                        <select id="baggage" class="form-control" name="guestDepartBaggage[<?php echo $i; ?>]">
                            <?php
                            foreach ($baggageSelections as $value => $text) {
                                echo "<option " . ($value == 0 ? 'selected' : '') . " value='$value'>$text</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <?php
                if (isset($_SESSION['returnFlight'])) {
                ?>
                    <div class="col-2">
                        <h6 class="mt-2">Return Flight (<?php echo $returnFlightInfo['registration']; ?>)</h6>
                        <div class="form-group">
                            <label for="baggage">Baggage</label>
                            <select id="baggage" class="form-control" name="guestReturnBaggage[<?php echo $i; ?>]">
                                <?php
                                foreach ($baggageSelections as $value => $text) {
                                    echo "<option " . ($value == 0 ? 'selected' : '') . " value='$value'>$text</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>

        <?php
        }
        $connection->close();
        ?>
        <input type="submit" value="Next" class="btn btn-primary">
    </form>


    <?php require('scripts.php') ?>
</body>

</html>