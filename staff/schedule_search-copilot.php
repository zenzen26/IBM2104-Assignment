<?php require('validateLogin.php'); ?>
<html>

<head>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet" />
    <?php require('../head.php');
    require('navbar.php'); ?>
    <title>Flight Team | Search Co-Pilot</title>
</head>

<body>
    <div class="container">
        <h3>Flight Team | Search Co-Pilot</h3>
        <p>Please enter the co-pilot's name or their staff id to search for a pilot</p>
        <?php $scheduleID = $_GET['id']; ?>

        <form action="schedule_search-copilot.php" method="get">
            <div class="form-row">
                <!-- Flight Number Search -->
                <div class="form-group col-md-6">
                    <label for="pilotName">Co-Pilot Name</label>
                    <input type="text" class="form-control" name="coPilotName">
                </div>

                <!-- Flight Status Search -->
                <div class="form-group col-md-6">
                    <label for="staffID">Staff ID</label>
                    <input type="text" class="form-control" name="staffID">
                </div>
            </div>
            <input type="hidden" class="form-control" name="id" value=<?php echo $_GET['id'] ?>>
            <input type="submit" class="btn btn-primary" value="Search">
        </form>

        <?php
        //Connection to database establish
        $connection = new mysqli("localhost", "admin", null, "ibm2104_assignment");
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        echo '<table class = "table">';
        echo '<thead class="thead-light">';
        echo '<tr>';
        echo '<th scope="col">Staff ID</th>';
        echo '<th scope="col">Staff Name</th>';
        echo '<th scope="col">Email</th>';
        echo '</tr>';
        echo '</thead>';

        $staffQuery = "SELECT * FROM staff WHERE role = 1";

        if (!empty($_GET['coPilotName'])) {
            $filterString[] = "staff_name LIKE '%" . $_GET['coPilotName'] . "%'";
        }

        if (!empty($_GET['staffID'])) {
            $filterString[] = "id = '" . $_GET['staffID'] . "'";
        }

        if (!empty($filterString)) {
            $staffQuery = $staffQuery . " AND " . implode(" AND ", $filterString);
        }

        $resultStaffQuery = $connection->query($staffQuery);
        while ($staffDetails = mysqli_fetch_assoc($resultStaffQuery)) {
            echo "<tr><td>" . $staffDetails['id'] . "</td>";
            echo "<td>" . $staffDetails['staff_name'] . "</td>";
            echo "<td>" . $staffDetails['email'] . "</td>";
            echo "</tr>";
        }
        echo '</table>';
        $connection->close();
        ?>
    </div>


</body>

</html>