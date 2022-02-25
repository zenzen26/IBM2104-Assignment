<?php require('validateLogin.php'); ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>Sales of Flights Report</title>
</head>

<body class="d-flex flex-column h-100">

    <?php require('navbar.php');

    $connection = new mysqli("127.0.0.1", "admin", null, "ibm2104_assignment");

    if ($_GET['reportType'] == "Ticket Sales Report") {
        $totalTickets = [];
        $ticketClasses = $connection->query("SELECT * FROM ticket_class");
        if ($ticketClasses->num_rows > 0) {
            while ($ticketClass = $ticketClasses->fetch_assoc()) {
                $totalTickets[$ticketClass['id']]['name'] = $ticketClass['name'];
                $totalTickets[$ticketClass['id']]['quantity'] = $connection->query("SELECT COUNT(*) as quantity FROM flight_tickets WHERE class = " . $ticketClass['id'])->fetch_assoc()['quantity'];
                $totalTickets[$ticketClass['id']]['revenue'] = $totalTickets[$ticketClass['id']]['quantity'] * $ticketClass['price'];
            }
        }
    } else {
        $query = "SELECT flights.id, flights.type, flights.departure, flights.arrival, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime, flight_schedules.status FROM flight_schedules INNER JOIN flights on flight_schedules.flight_no = flights.id WHERE flight_schedules.id = " . $_GET['id'];
        $flightScheduleStatisticResult = $connection->query("SELECT COUNT(*) as totalTickets, SUM(price) as totalRevenue FROM flight_tickets WHERE schedule_no = " . $_GET['id'])->fetch_assoc();
        $result = $connection->query($query);
        $report = $result->fetch_assoc();
    }
    ?>

    <div class="container">
        <div class="row my-4">
            <?php
            //filter
            if ($_GET['reportType'] == "Flight Report") {
                echo "<h3 class=col>Monitoring Flights | Report</h3>";
            ?>
        </div>
        <div class="row">
            <div class="card" style="width:100%">
                <div class="card-body">
                    <span>Flight Schedule Number: </span>
                    <span><?php echo $report['id'] ?></span>
                </div>
                <div class="card-body">
                    <span>Flight Type: </span>
                    <span><?php echo $report['type'] ?></span>
                </div>
                <div class="row card-body">
                    <div class="col">
                        <span>Departure Location: </span>
                        <span><?php echo $report['departure'] ?></span>
                    </div>
                    <div class="col">
                        <span>Arrival Location: </span>
                        <span><?php echo $report['arrival'] ?></span>
                    </div>
                </div>
                <div class="row card-body">
                    <div class="col">
                        <span>Flight Arrival Date: </span>
                        <span><?php echo $report['arrive_dateTime'] ?></span>
                    </div>
                    <div class="col">
                        <span>Flight Departure Date: </span>
                        <span><?php echo $report['depart_dateTime'] ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <span>Flight Status: </span>
                    <span><?php echo $report['status'] ?></span>
                </div>
            </div>
            <div class="row my-4">
            <?php
            } else if ($_GET['reportType'] == "Sales Report") {
                echo "<h3 class=col>Monitoring Sales of Flights | Report</h3>";
            ?>
            </div>
            <div class="row">
                <div class="card" style="width:100%">
                    <div class="card-body">
                        <span>Flight Number: </span>
                        <span><?php echo $_GET['id'] ?></span>
                    </div>
                    <div class="card-body">
                        <span>Flight Type: </span>
                        <span><?php echo $report['type'] ?></span>
                    </div>
                    <div class="row card-body">
                        <div class="col">
                            <span>Departure Location: </span>
                            <span><?php echo $report['departure'] ?></span>
                        </div>
                        <div class="col">
                            <span>Arrival Location: </span>
                            <span><?php echo $report['arrival'] ?></span>
                        </div>
                    </div>
                    <div class="row card-body" ?>
                        <div class="col">
                            <span>Number of Bookings: </span>
                            <span><?php echo $flightScheduleStatisticResult['totalTickets'] ?></span>
                        </div>
                        <div class="col">
                            <span>Total Revenue: </span>
                            <span><?php echo empty($flightScheduleStatisticResult['totalRevenue']) ? 0 : $flightScheduleStatisticResult['totalRevenue'] ?> MYR</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-4">
        <?php
            } else if ($_GET['reportType'] == "Ticket Sales Report") {
                echo "<h3 class=col>Monitoring Sales of Flights | Report</h3>";
        ?>
        </div>
        <div class="row">
            <div class="card" style="width:100%">
                <?php foreach ($totalTickets as $ticketData) { ?>
                    <div class="row card-body" ?>
                        <div class="col">
                            <span>Ticket Class: </span>
                            <span><?php echo $ticketData['name'] ?></span>
                        </div>
                        <div class="col">
                            <span>Revenue of Ticket Class: </span>
                            <span><?php echo empty($ticketData['revenue']) ? 0 : $ticketData['revenue'] ?> MYR</span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php
            } ?>
    </div>
    </div>

    <?php require('../scripts.php') ?>
</body>

</html>