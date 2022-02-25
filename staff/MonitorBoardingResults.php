<?php require('validateLogin.php'); ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<?php $connrction = new mysqli("127.0.0.1", "admin", null, "ibm2104_assignment"); ?>

<head>
    <?php require('../head.php') ?>
    <title>Boarding Search Results</title>
</head>

<body class="d-flex flex-column h-100">
    <?php require('navbar.php');

    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');
    $query = "SELECT flight_tickets.seat_no, flight_tickets.passenger_name, flight_tickets.ic_passport, flight_tickets.status, flight_tickets.schedule_no FROM flight_tickets";

    $filterString = [];

    if (!empty($_GET['id'])) {
        $filterString[] = "schedule_no = '" . ($_GET['id']) . "'";
    }

    if (!empty($filterString)) {
        $query = $query . " WHERE " . implode(" AND ", $filterString);
    }

    $result = $connection->query($query);
    ?>

    <div class="container">
        <div class="row my-4">
            <h3 class="col">Monitoring of Boarding | Passenger Results</h3>
        </div>
        <form>
            <div class="row">
                <table class="table table-hover col">
                    <thead>
                        <tr>
                            <th class="text-center">Seat Number</th>
                            <th class="text-center">Passenger Name</th>
                            <th class="text-center">Passport Number</th>
                            <th class="text-center">Status</th>
                        </tr>
                        <?php
                        while ($flight = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class = text-center>" . $flight['seat_no'] . "</td>";
                            echo "<td class = text-center>" . $flight['passenger_name'] . "</td>";
                            echo "<td class = text-center>" . $flight['ic_passport'] . "</td>";
                            echo "<td class = text-center>" . $flight['status'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </thead>
                </table>
            </div>
        </form>
    </div>

    <?php require('../scripts.php') ?>
</body>

</html>