<?php require('validateLogin.php') ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>AirAsia Staff Portal | Flight Schedule Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body>
    <?php
    $page = 'flights';
    require('navbar.php');

    // Create connection with MySQL database
    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Query for getting total number of records
    $totalRecordsQuery = "SELECT COUNT(*) FROM flight_schedules INNER JOIN flights ON flight_schedules.flight_no = flights.id";

    // Select all flight schedules
    $query = "SELECT flight_schedules.*, flights.registration, flights.departure, flights.arrival FROM flight_schedules INNER JOIN flights ON flight_schedules.flight_no = flights.id";

    $filterString = [];

    if (isset($_GET['flightRegistration']) && $_GET['flightRegistration'] !== "") {
        $filterString[] = "registration LIKE '%" . $_GET['flightRegistration'] . "%'";
    }

    if (isset($_GET['flightStatus']) && $_GET['flightStatus'] != "Any") {
        $filterString[] = "status = '" . $_GET['flightStatus'] . "'";
    }

    if (!empty($_GET['departDate'])) {
        $filterString[] = "DATE(flight_schedules.depart_dateTime) = '" . date("Y-m-d", strtotime($_GET['departDate'])) . "'";
    }

    if (!empty($_GET['arrivalDate'])) {
        $filterString[] = "DATE(flight_schedules.arrive_dateTime) = '" . date("Y-m-d", strtotime($_GET['arrivalDate'])) . "'";
    }

    if (!empty($_GET['departLocation']) && $_GET['departLocation'] != "Any") {
        $filterString[] = "flights.departure = '" . $_GET['departLocation'] . "'";
    }

    if (!empty($_GET['arrivalLocation']) && $_GET['arrivalLocation'] != "Any") {
        $filterString[] = "flights.arrival = '" . $_GET['arrivalLocation'] . "'";
    }

    if (!empty($filterString)) {
        // Add WHERE based on filters
        $filterStatement = " WHERE " . implode(" AND ", $filterString);
        $totalRecordsQuery = $totalRecordsQuery . $filterStatement;
        $query = $query . $filterStatement;
    }

    // Default page no
    $pageNo = 1;
    if (!empty($_GET['page'])) {
        $pageNo = $_GET['page'];
    }

    $recordsPerPage = 5;
    $recordOffset = ($pageNo - 1) * $recordsPerPage;
    $totalRecords = $connection->query($totalRecordsQuery)->fetch_array()[0];
    $totalPages = ceil($totalRecords / $recordsPerPage);

    if ($totalRecords > 0) {
        $query = $query . " LIMIT $recordOffset, $recordsPerPage";
        $result = $connection->query($query);
    }

    // Declare list of airports
    $airports = [
        "Any",
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

    $flightStatuses = [
        "Any",
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
        <h3>Flight Schedules | Search</h3>
        <form action="flight-schedule-search.php" method="GET">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="flightRegistration">Flight Registration</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="text" class="form-control" value="<?php echo $_GET['flightRegistration'] ?? ""; ?>" id="flightRegistration" name="flightRegistration">
                </div>
                <div class="form-group col-3">
                    <label for="flightStatus">Flight Status</label>
                    <select id="flightStatus" class="form-control" name="flightStatus">
                        <?php
                        foreach ($flightStatuses as $flightStatus) {
                            echo "<option " . ((isset($_GET['flightStatus']) && $_GET['flightStatus'] == $flightStatus) ? "selected" : "") . ">$flightStatus</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="departDate">Departure Date</label>
                    <div class="input-group date" id="departDatePicker" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input" value="<?php echo $_GET['departDate'] ?? ""; ?>" data-target="#departDatePicker" id="departDate" name="departDate" />
                        <div class="input-group-append" data-target="#departDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-3">
                    <label for="arrivalDate">Arrival Date</label>
                    <div class="input-group date" id="arrivalDatePicker" data-target-input="nearest">
                        <!-- Display previous data if there is previous data -->
                        <input type="text" class="form-control datetimepicker-input" value="<?php echo $_GET['arrivalDate'] ?? ""; ?>" data-target="#arrivalDatePicker" id="arrivalDate" name="arrivalDate" />
                        <div class="input-group-append" data-target="#arrivalDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="departLocation">Departure Location</label>
                    <select id="departLocation" class="form-control" name="departLocation">
                        <?php
                        foreach ($airports as $airport) {
                            echo "<option " . ((isset($_GET['departLocation']) && $_GET['departLocation'] == $airport) ? "selected" : "") . ">$airport</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-3">
                    <label for="arrivalLocation">Arrival Location</label>
                    <select id="arrivalLocation" class="form-control" name="arrivalLocation">
                        <?php
                        foreach ($airports as $airport) {
                            echo "<option " . ((isset($_GET['arrivalLocation']) && $_GET['arrivalLocation'] == $airport) ? "selected" : "") . ">$airport</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <?php if ($totalRecords > 0) { ?>
            <table class="table table-hover mt-5 border-bottom">
                <thead>
                    <tr>
                        <th>Flight Schedule Number</th>
                        <th>Flight Registration</th>
                        <th>Departure Date and Time</th>
                        <th>Arrival Date and Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <?php
                while ($flightSchedule = $result->fetch_assoc()) {
                    echo '<tr style="cursor: pointer;" onclick="window.location = \'' . 'flight-schedule.php?id=' . $flightSchedule['id'] . '\';">';
                    echo "<td>" . $flightSchedule['id'] . "</td>";
                    echo "<td>" . $flightSchedule['registration'] . "</td>";
                    echo "<td>" . $flightSchedule['depart_dateTime'] . "</td>";
                    echo "<td>" . $flightSchedule['arrive_dateTime'] . "</td>";
                    echo "<td>" . $flightSchedule['status'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <nav>
                <?php
                // Unset the page GET parameter first as clicking the page buttons below will provide a new page GET parameter
                unset($_GET['page']);
                $queryString = http_build_query($_GET);
                ?>
                <ul class="pagination justify-content-end">
                    <li class="page-item  <?php echo $pageNo <= 1 ? "disabled" : ""; ?>">
                        <?php
                        if ($pageNo <= 1) {
                            echo '<span class="page-link">Previous</span>';
                        } else {
                            echo '<a class="page-link" href="?page=' . ($pageNo - 1) . "&" . $queryString . '">Previous</a>';
                        }
                        ?>
                    </li>
                    <?php
                    for ($i = 1; $i <= $totalPages; $i++) {
                        echo '<li class="page-item ' . (($pageNo == $i) ? "active" : "") . '"><a class="page-link" href="?page=' . $i . "&" . $queryString . '">' . $i . '</a></li>';
                    }
                    ?>
                    <li class="page-item <?php echo $pageNo >= $totalPages ? "disabled" : ""; ?>">
                        <?php
                        if ($pageNo >= $totalPages) {
                            echo '<span class="page-link">Next</span>';
                        } else {
                            echo '<a class="page-link" href="?page=' . ($pageNo + 1) . "&" . $queryString . '">Next</a>';
                        }
                        ?>
                    </li>
                </ul>
            </nav>
        <?php
        } else {
            echo "<p class='mt-4'>No flight schedules found</p>";
        }
        $connection->close();
        ?>
    </div>

    <?php require('../scripts.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(function() {
            $('#departDatePicker').datetimepicker({
                format: 'DD-MM-YYYY',
            });
            $('#arrivalDatePicker').datetimepicker({
                format: 'DD-MM-YYYY',
            });
        });
    </script>
</body>

</html>