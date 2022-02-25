<?php require('validateLogin.php') ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>Flight Schedule | Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
    <style>
        tr {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php
    require('navbar.php');

    $connection = new mysqli("localhost", "admin", null, "ibm2104_assignment");
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Select all flight schedules that
    $query = "SELECT flight_schedules.*, flights.registration, flights.departure, flights.arrival FROM flight_schedules INNER JOIN flights ON flight_schedules.flight_no = flights.id";

    $filterString = [];

    if (isset($_GET['flightNumber']) && $_GET['flightNumber'] !== "") {
        $filterString[] = "flight_no =" . $_GET['flightNumber'];
    }

    if (isset($_GET['flightStatus'])) {
        if ($_GET['flightStatus'] == "Any") {
            $filterString[] = "(status = 'Scheduled' OR status = 'Delayed')";
        } else {
            $filterString[] = "status = '" . $_GET['flightStatus'] . "'";
        }
    }

    if (!empty($_GET['departDate'])) {
        $filterString[] = "DATE(flight_schedules.depart_dateTime) = '" . date("Y-m-d", strtotime($_GET['departDate'])) . "'";
    }

    if (!empty($_GET['arrivalDate'])) {
        $filterString[] = "DATE(flight_schedules.arrive_dateTime) = '" . date("Y-m-d", strtotime($_GET['arrivalDate'])) . "'";
    }

    if (!empty($_GET['departLocation'])) {
        $filterString[] = "flights.departure = '" . $_GET['departLocation'] . "'";
    }

    if (!empty($_GET['arrivalLocation'])) {
        $filterString[] = "flights.arrival = '" . $_GET['arrivalLocation'] . "'";
    }

    if (!empty($filterString)) {
        $query = $query . " WHERE " . implode(" AND ", $filterString);
    }

    $result = $connection->query($query);

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

    $flightStatuses = [
        "Any",
        "Scheduled",
        "Delayed"
    ]
    ?>
    <div class="container">
        <h3>Flight Schedules | Search</h3>
        <form action="schedule_search-1.php" method="GET">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="flightNumber">Flight Number</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="text" class="form-control" value="<?php echo $_GET['flightNumber'] ?? ""; ?>" id="flightNumber" name="flightNumber">
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
                        <option label=" "></option>
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
                        <option label=" "></option>
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
        <?php if ($result->num_rows > 0) { ?>
            <table class="table table-hover mt-5 border-bottom">
                <thead>
                    <tr>
                        <th>Flight Number</th>
                        <th>Departure Date and Time</th>
                        <th>Arrival Date and Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <?php
                while ($flightSchedule = $result->fetch_assoc()) {
                    echo '<tr onclick="window.location = \'' . 'flightTeam_search.php?id=' . $flightSchedule['id'] . '\';">';
                    echo "<td>" . $flightSchedule['flight_no'] . "</td>";
                    echo "<td>" . $flightSchedule['depart_dateTime'] . "</td>";
                    echo "<td>" . $flightSchedule['arrive_dateTime'] . "</td>";
                    echo "<td>" . $flightSchedule['status'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        <?php
        } else {
            echo "<p class='mt-4'>No flight schedules found</p>";
        }
        ?>
    </div>

    <?php
    $connection->close();
    require('../scripts.php')
    ?>
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