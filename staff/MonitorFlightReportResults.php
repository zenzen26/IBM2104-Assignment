<?php
require('validateLogin.php');
if ($_GET['reportType'] == "Ticket Sales Report") {
    header('Location: MonitorFlightReport.php?reportType=Ticket Sales Report');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>Sales of Flight Search Results</title>
</head>

<body class="d-flex flex-column h-100">
    <?php require('navbar.php');

    $connection = new mysqli("127.0.0.1", "admin", null, "ibm2104_assignment");
    $query = "SELECT flights.type, flights.departure, flights.arrival,flight_schedules.id, flight_schedules.flight_no, flight_schedules.status, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime  FROM flight_schedules INNER JOIN flights on flight_schedules.flight_no = flights.id";

    $filterString = [];

    if (isset($_GET['flightType']) && $_GET['flightType'] !== "Any Type") {
        $filterString[] = "type = '" . $_GET['flightType'] . "'";
    }

    if (isset($_GET['flightStatus']) && $_GET['flightStatus'] !== "Any Status") {
        $filterString[] = "status = '" . $_GET['flightStatus'] . "'";
    }

    if (isset($_GET['departureLocation']) && $_GET['departureLocation'] !== "Any Location") {
        $filterString[] = "flights.departure = '" . $_GET['departureLocation'] . "'";
    }

    if (isset($_GET['arrivalLocation']) && $_GET['arrivalLocation'] !== "Any Location") {
        $filterString[] = "flights.arrival = '" . $_GET['arrivalLocation'] . "'";
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
            <h3 class="col">Monitoring Sales of Flights | Results</h3>
        </div>
        <form action="MonitorFlightSalesReport.php" method="GET">
            <div class="row">
                <table class="table table-hover col">
                    <thead>
                        <tr>
                            <th class="text-center">Flight Schedule No.</th>
                            <th class="text-center">Flight Type</th>
                            <th class="text-center">Departure Location</th>
                            <th class="text-center">Arrival Location</th>
                            <th class="text-center">Flight Departure Date and Time</th>
                            <th class="text-center">Flight Arrival Date and Time</th>
                            <th class="text-center">Flight Status</th>
                        </tr>
                    </thead>
                    <?php
                    while ($flight = $result->fetch_assoc()) {
                        echo "<tr style=cursor:pointer onclick=\"window.location = 'MonitorFlightReport.php?id=" . $flight['id'] . "&reportType=" . $_GET['reportType'] . "'\">";
                        echo "<td class = text-center>" . $flight['flight_no'] . "</td>";
                        echo "<td class = text-center>" . $flight['type'] . "</td>";
                        echo "<td class = text-center>" . $flight['departure'] . "</td>";
                        echo "<td class = text-center>" . $flight['arrival'] . "</td>";
                        echo "<td class = text-center>" . $flight['depart_dateTime'] . "</td>";
                        echo "<td class = text-center>" . $flight['arrive_dateTime'] . "</td>";
                        echo "<td class = text-center>" . $flight['status'] . "</td>";
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