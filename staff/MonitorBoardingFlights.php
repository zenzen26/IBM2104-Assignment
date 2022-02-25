<?php require('validateLogin.php'); ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>Boarding Search Results</title>
</head>

<body class="d-flex flex-column h-100">
    <?php require('navbar.php');

    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');
    $query = "SELECT flight_schedules.id, flight_schedules.flight_no, flight_schedules.status, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime FROM flight_schedules";

    $filterString = [];

    if (!empty($_GET['flightNumber'])) {
        $filterString[] = "id = '" . ($_GET['flightNumber']) . "'";
    }

    if (isset($_GET['flightStatus']) && $_GET['flightStatus'] !== "Any Status") {
        $filterString[] = "status = '" . $_GET['flightStatus'] . "'";
    }

    if (!empty($_GET['departureDate'])) {
        $filterString[] = "DATE(flight_schedules.depart_dateTime) = '" . date("Y-m-d", strtotime($_GET['departureDate'])) . "'";
    }

    if (!empty($_GET['arrivalDate'])) {
        $filterString[] = "DATE(flight_schedules.arrive_dateTime) = '" . date("Y-m-d", strtotime($_GET['arrivalDate'])) . "'";
    }

    if (!empty($filterString)) {
        $query = $query . " WHERE " . implode(" AND ", $filterString);
    }

    $result = $connection->query($query);
    ?>

    <div class="container">
        <div class="row my-4">
            <h3 class="col">Monitoring of Boarding | Flight Results</h3>
        </div>
        <form>
            <div class="row">
                <table class="table table-hover col">
                    <thead>
                        <tr>
                            <th class="text-center">Flight Schedule Number</th>
                            <th class="text-center">Flight Status</th>
                            <th class="text-center">Departure Date</th>
                            <th class="text-center">Arrival Date</th>
                        </tr>
                    </thead>
                    <?php
                    while ($flight = $result->fetch_assoc()) {
                        echo "<tr style=cursor:pointer onclick=\"window.location = 'MonitorBoardingResults.php?id=" . $flight['id'] . "'\">";
                        echo "<td class = text-center>" . $flight['id'] . "</td>";
                        echo "<td class = text-center>" . $flight['status'] . "</td>";
                        echo "<td class = text-center>" . $flight['depart_dateTime'] . "</td>";
                        echo "<td class = text-center>" . $flight['arrive_dateTime'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </form>
    </div>

    <?php require('../scripts.php') ?>
</body>

</html>