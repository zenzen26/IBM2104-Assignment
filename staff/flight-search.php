<?php require('validateLogin.php') ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>AirAsia Staff Portal | Flight Search</title>
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
    $totalRecordsQuery = "SELECT COUNT(*) FROM flights";

    // Select all flights
    $query = "SELECT * FROM flights";

    $filterString = [];

    if (isset($_GET['flightRegistration']) && $_GET['flightRegistration'] !== "") {
        $filterString[] = "registration LIKE '%" . $_GET['flightRegistration'] . "%'";
    }

    if (isset($_GET['flightType']) && $_GET['flightType'] != "Any") {
        $filterString[] = "type  = '" . $_GET['flightType'] . "'";
    }

    if (!empty($_GET['departLocation']) && $_GET['departLocation'] != "Any") {
        $filterString[] = "departure = '" . $_GET['departLocation'] . "'";
    }

    if (!empty($_GET['arrivalLocation']) && $_GET['arrivalLocation'] != "Any") {
        $filterString[] = "arrival = '" . $_GET['arrivalLocation'] . "'";
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

    $flightTypes = [
        "Any",
        "Domestic",
        "International"
    ]
    ?>
    <div class="container-fluid p-3">
        <h3>Flights | Search</h3>
        <form action="flight-search.php" method="GET">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="flightRegistration">Flight Registration</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="text" class="form-control" value="<?php echo $_GET['flightRegistration'] ?? ""; ?>" id="flightRegistration" name="flightRegistration">
                </div>
                <div class="form-group col-3">
                    <label for="flightType">Flight Type</label>
                    <select id="flightType" class="form-control" name="flightType">
                        <?php
                        foreach ($flightTypes as $flightType) {
                            echo "<option " . ((isset($_GET['flightType']) && $_GET['flightType'] == $flightType) ? "selected" : "") . ">$flightType</option>";
                        }
                        ?>
                    </select>
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
                        <th>Flight Number</th>
                        <th>Flight Registration</th>
                        <th>Flight Type</th>
                        <th>Departure Location</th>
                        <th>Arrival Location</th>
                    </tr>
                </thead>
                <?php
                while ($flight = $result->fetch_assoc()) {
                    echo '<tr style="cursor: pointer;" onclick="window.location = \'' . 'flight.php?id=' . $flight['id'] . '\';">';
                    echo "<td>" . $flight['id'] . "</td>";
                    echo "<td>" . $flight['registration'] . "</td>";
                    echo "<td>" . $flight['type'] . "</td>";
                    echo "<td>" . $flight['departure'] . "</td>";
                    echo "<td>" . $flight['arrival'] . "</td>";
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
            echo "<p class='mt-4'>No flights found</p>";
        }
        $connection->close();
        ?>
    </div>

    <?php require('../scripts.php') ?>
</body>

</html>