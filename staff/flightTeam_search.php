<?php require('validateLogin.php'); ?>
<html>

<head>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet" />
    <?php require('../head.php');
    require('navbar.php'); ?>
    <title>Flight Schedule | Pilots and Crews</title>
</head>

<?php
//Connection to database establish
$connection = new mysqli("localhost", "admin", null, "ibm2104_assignment");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$allowSave = array();
$allowSaveQuery = array();

if (isset($_GET['id']) && $_GET['id'] !== "") {
    //Display the details of the selected flight schedule
    $scheduleID = $_GET['id'];
    $query = "SELECT flight_schedules.*, flights.registration, flights.departure, flights.arrival FROM flight_schedules INNER JOIN flights ON flight_schedules.flight_no = flights.id WHERE flight_schedules.id = $scheduleID";
    $result = $connection->query($query);
    $displayDetails = array();
    while ($resultDetails = mysqli_fetch_assoc($result)) {
        $displayDetails['flightNo'] = $resultDetails['flight_no'];
        $displayDetails['departLocation'] = $resultDetails['departure'];
        $displayDetails['arriveLocation'] = $resultDetails['arrival'];
        $displayDetails['departDateTime'] = $resultDetails['depart_dateTime'];
        $displayDetails['arriveDateTime'] = $resultDetails['arrive_dateTime'];
    }



    //Get the selected departDateTime and arrivalDateTime
    $query = "SELECT depart_dateTime, arrive_dateTime FROM flight_schedules WHERE id = $scheduleID";
    $result = $connection->query($query);
    $scheduleDateTime = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $scheduleDateTime['depart_dateTime'] = $row['depart_dateTime'];
        $scheduleDateTime['arrive_dateTime'] = $row['arrive_dateTime'];
    }
    $departTime = strtotime($scheduleDateTime['depart_dateTime']);
    $arriveTime = strtotime($scheduleDateTime['arrive_dateTime']);

    //Check pilot condition 
    if (isset($_GET['pilotID']) && $_GET['pilotID'] !== "") {
        $pilot = $_GET['pilotID'];
        $checkRoleQuery = "SELECT * FROM staff WHERE id = $pilot AND role = 1";
        $resultScheck = $connection->query($checkRoleQuery);
        $allowSave['pilot'] = 1;
        $allowSaveQuery['pilot'] = $pilot;
        if (!empty($resultScheck) && $resultScheck->num_rows > 0) {
            $query = "SELECT staff.id, flight_teams.schedule_no, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime FROM staff INNER JOIN flight_teams ON staff.id = flight_teams.pilot OR staff.id = flight_teams.co_pilot INNER JOIN flight_schedules ON flight_teams.schedule_no = flight_schedules.id WHERE staff.id = $pilot";
            $result = $connection->query($query);
            if (!empty($result) && $result->num_rows > 0) {
                while ($staffSchedules = mysqli_fetch_assoc($result)) {
                    if ($staffSchedules['schedule_no'] == $scheduleID) {
                        $allowSave['pilot'] = 1;
                        break;
                    } else if (strtotime($staffSchedules['depart_dateTime']) <= $arriveTime && strtotime($staffSchedules['arrive_dateTime']) >= $departTime) {
                        $allowSave['pilot'] = 0;
                        unset($allowSaveQuery);
                        break;
                    }
                }
            }
        } else {
            $allowSave['pilot'] = 0;
            unset($allowSaveQuery);
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
            echo "<strong>Not Pilot!</strong> Please select a staff ID with a pilot role.";
            echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
            echo "<span aria-hidden='true'>&times;</span>";
            echo "</button>";
            echo "</div>";
        }
    }

    //check co pilot condition
    if (isset($_GET['coPilotID']) && $_GET['coPilotID'] !== "") {
        $copilot = $_GET['coPilotID'];
        $checkRoleQuery = "SELECT * FROM staff WHERE id = $copilot AND role = 1";
        $allowSave['copilot'] = 1;
        $allowSaveQuery['copilot'] = $copilot;
        $resultScheck = $connection->query($checkRoleQuery);
        if (!empty($resultScheck) && $resultScheck->num_rows > 0) {
            $query = "SELECT staff.id, flight_teams.schedule_no, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime FROM staff INNER JOIN flight_teams ON staff.id = flight_teams.pilot OR staff.id = flight_teams.co_pilot INNER JOIN flight_schedules ON flight_teams.schedule_no = flight_schedules.id WHERE staff.id = $copilot";
            $result = $connection->query($query);
            if (!empty($result) && $result->num_rows > 0) {
                while ($staffSchedules = mysqli_fetch_assoc($result)) {
                    if ($staffSchedules['schedule_no'] == $scheduleID) {
                        $allowSave['copilot'] = 1;
                        break;
                    } else if (strtotime($staffSchedules['depart_dateTime']) <= $arriveTime && strtotime($staffSchedules['arrive_dateTime']) >= $departTime) {
                        $allowSave['copilot'] = 0;
                        unset($allowSaveQuery['copilot']);
                        break;
                    }
                }
            }
        } else {
            $allowSave['copilot'] = 0;
            unset($allowSaveQuery['copilot']);
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
            echo "<strong>Not Pilot!</strong> Please select a staff ID with a pilot role.";
            echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
            echo "<span aria-hidden='true'>&times;</span>";
            echo "</button>";
            echo "</div>";
        }
    }

    if (isset($_GET['flightAttendantID']) && $_GET['flightAttendantID'] !== "") {
        $attendants = $_GET['flightAttendantID'];
        foreach ($attendants as $attendant) {
            $checkRoleQuery = "SELECT * FROM staff WHERE id = $attendant AND role = 2";
            $resultScheck = $connection->query($checkRoleQuery);
            $allowSave['attendant'][$attendant] = 1;
            $allowSaveQuery['attendant'][$attendant] = $attendant;
            if (!empty($resultScheck) && $resultScheck->num_rows > 0) {
                $query = "SELECT flight_attendant_team.*, flight_schedules.id, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime FROM flight_attendant_team INNER JOIN flight_teams ON flight_attendant_team.flight_team = flight_teams.id INNER JOIN flight_schedules ON flight_teams.schedule_no = flight_schedules.id WHERE flight_attendant_team.flight_attendant = $attendant";
                $result = $connection->query($query);
                if (!empty($result) && $result->num_rows > 0) {
                    while ($staffSchedules = mysqli_fetch_assoc($result)) {
                        if ($staffSchedules['id'] == $scheduleID) {
                            $allowSave['attendant'][$attendant] = 1;
                            break;
                        } else if (strtotime($staffSchedules['depart_dateTime']) <= $arriveTime && strtotime($staffSchedules['arrive_dateTime']) >= $departTime) {
                            $allowSave['attendant'][$attendant] = 0;
                            unset($allowSaveQuery['attendant'][$attendant]);
                            break;
                        }
                    }
                }
            } else {
                $allowSave['attendant'][$attendant] = 0;
                unset($allowSaveQuery['attendant'][$attendant]);
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
                echo "<strong>Not Flight Attendant!</strong> Please select a staff ID with a flight attendant role.";
                echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
                echo "<span aria-hidden='true'>&times;</span>";
                echo "</button>";
                echo "</div>";
            }
        }
    }

    //Check if pilot and co-pilot are the same person or not
    if (isset($_GET['pilotID']) && $_GET['pilotID'] !== "" && isset($_GET['coPilotID']) && $_GET['coPilotID'] !== "" && $_GET['pilotID'] == $_GET['coPilotID']) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
        echo "<strong>Error submitting!</strong> Pilot and co-pilot cannot be the same.";
        echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
        echo "<span aria-hidden='true'>&times;</span>";
        echo "</button>";
        echo "</div>";

        $allowSave['pilot'] = 0;
        $allowSave['copilot'] = 0;
    }

    //If there is no flight attendant
    if (empty($_GET['flightAttendantID']) && !empty($_GET['pilotID']) && !empty($_GET['coPilotID'])) {
        $allowSave['attendant'][0] = 0;
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
        echo "<strong>Error submitting!</strong> There must be atleast one flight attendant.";
        echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
        echo "<span aria-hidden='true'>&times;</span>";
        echo "</button>";
        echo "</div>";
    }

    //Save to database
    if (!empty($allowSave) && !empty($allowSaveQuery)) {
        $update = 1;
        if ($allowSave["pilot"] == 1 && $allowSave["copilot"] == 1) {
            foreach ($allowSave['attendant'] as $a) {
                if ($a !== 1) {
                    $update = 0;
                    break;
                }
            }
        } else {
            $update = 0;
        }

        if ($update == 1) {
            $role = $connection->query("SELECT role FROM staff WHERE id = " . $_SESSION['userId'])->fetch_assoc()['role'];
            $allow = false;

            if ($role == "Superior" || $role == "Manager") {
                // User is supervisor or manager, allow them to perform action
                $allow = true;
            } else {
                if (isset($_POST['username']) && isset($_POST['password'])) {
                    $result = $connection->query("SELECT * FROM staff WHERE email = '" . $_POST['username'] . "' AND password = '" . $_POST['password'] . "' AND (role = 'Superior' OR role = 'Manager');");

                    if ($result->num_rows > 0) {
                        // The username and password is valid, allow them to perform action
                        $allow = true;
                    } else {
                        // The username and password is invalid, show error message
                        $showApprovalModal = true;
                        $invalidCredentials = true;
                    }
                } else {
                    // Request for supervisor or manager approval
                    $showApprovalModal = true;
                }
            }
            if ($allow) {
                $sqlPilot = $allowSaveQuery['pilot'];
                $sqlCoPilot = $allowSaveQuery['copilot'];
                $query = "SELECT * FROM flight_teams WHERE schedule_no = $scheduleID";
                $result = $connection->query($query);

                if (!empty($result) && $result->num_rows > 0) {
                    //UPDATE TABLE
                    $result = $connection->query($query);
                    $resultDetails = mysqli_fetch_assoc($result);
                    $teamID = $resultDetails['id'];
                    $updatePilot = "UPDATE flight_teams SET pilot=$sqlPilot WHERE id = $teamID";
                    $result = $connection->query($updatePilot);
                    $updateCoPilot = "UPDATE flight_teams SET co_pilot=$sqlCoPilot WHERE id = $teamID";
                    $result = $connection->query($updateCoPilot);
                    $connection->query("DELETE FROM flight_attendant_team WHERE flight_team = $teamID");
                    foreach ($allowSaveQuery['attendant'] as $saveQuery) {
                        $updateAttendant = "INSERT INTO flight_attendant_team (flight_team, flight_attendant) VALUE ($teamID, $saveQuery)";
                        $result = $connection->query($updateAttendant);
                    }
                } else {
                    //INSERT NEW TEAM
                    $insertPilots = "INSERT INTO flight_teams (schedule_no,pilot, co_pilot) VALUES ($scheduleID, $sqlPilot, $sqlCoPilot)";
                    $result = $connection->query($insertPilots);
                    $teamID = $connection->insert_id;
                    foreach ($allowSaveQuery['attendant'] as $saveQuery) {
                        $insertAttendant = "INSERT INTO flight_attendant_team (flight_team, flight_attendant) VALUE ($teamID, $saveQuery)";
                        $result = $connection->query($insertAttendant);
                    }
                }
            }
        }
    }
}

//Check if the team exisited
$teamQuery = "SELECT * FROM flight_teams WHERE schedule_no = $scheduleID";
$resultQuery = $connection->query($teamQuery);
$displayPilots = [];
$flightAttendants = $_GET['flightAttendantID'] ?? [];

if (!isset($_GET['flightAttendantID'])) {
    if ($resultQuery->num_rows > 0) {
        $flightTeam = mysqli_fetch_assoc($resultQuery);
        $displayPilots['pilot'] = $flightTeam['pilot'];
        $displayPilots['copilot'] = $flightTeam['co_pilot'];

        $flightAttendantTeams = $connection->query("SELECT * FROM flight_attendant_team WHERE flight_team = " . $flightTeam['id']);
        if ($flightAttendantTeams->num_rows > 0) {
            while ($flightAttendantTeam = mysqli_fetch_assoc($flightAttendantTeams)) {
                $flightAttendants[] = $flightAttendantTeam['flight_attendant'];
            }
        }
    }
}

?>

<body>
    <div class="container">
        <h3>Flight Schedule | Pilots and Crews</h3>
        <hr />
        <p>
            Flight Number: <?php echo $displayDetails['flightNo'] ?><br>
            Departure Location: <?php echo $displayDetails['departLocation'] ?><br>
            Arrival Location: <?php echo $displayDetails['arriveLocation'] ?><br>
            Departure Date & Time: <?php echo $displayDetails['departDateTime'] ?><br>
            Arrival Date & Time: <?php echo $displayDetails['arriveDateTime'] ?><br>
            <hr />
        </p>

        <!-- Search Form UI -->
        <form action="flightTeam_search.php" method="GET">
            <div class="form-row">
                <!-- Pilot Search -->
                <div class="form-group col-md-6">
                    <label for="pilot">Pilot</label>
                    <input type="text" class="form-control" name="pilotID" placeholder="Enter the staff ID for pilot" value="<?php echo $_GET['pilotID'] ?? $displayPilots['pilot'] ?? ""; ?>" required>
                    <a href="schedule_search-pilot.php?id=<?php echo $scheduleID ?>" target="_blank">Click here to view all available pilots</a>
                </div>

                <!-- Co-Pilot Search -->
                <div class="form-group col-md-6">
                    <label for="coPilot">Co-Pilot</label>
                    <input type="text" class="form-control" name="coPilotID" placeholder="Enter the staff ID for co-pilot" value="<?php echo $_GET['coPilotID'] ?? $displayPilots['copilot'] ?? ""; ?>" required>
                    <a href="schedule_search-copilot.php?id=<?php echo $scheduleID ?>" target="_blank">Click here to view all available co-pilots</a>
                </div>
            </div>

            <!-- Flight Attendant Search -->
            <button id="addRow" type="button" class="btn btn-primary">Add flight attendant</button><br>
            <a href="schedule_search-attendant.php?id=<?php echo $scheduleID ?>" target="_blank">Click here to view all available flight attendants</a><br>
            <div id="newRow">
                <?php foreach ($flightAttendants as $flightAttendant) { ?>
                    <div id="inputFormRow">
                        <div class="input-group mb-3">
                            <input type="text" name="flightAttendantID[]" class="form-control m-input" placeholder="Enter the staff ID for flight attendant" value="<?php echo $flightAttendant; ?>" required autocomplete="off">
                            <div class="input-group-append">
                                <button id="removeRow" type="button" class="btn btn-danger">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <input type="hidden" class="form-control" name="id" value=<?php echo $scheduleID ?>>
            <!-- Save Button -->
            <input type="submit" class="btn btn-success pull-right" value="Save Changes">
        </form>
        <?php if (isset($showApprovalModal)) { ?>
            <form action="flightTeam_search.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="POST">
                <div class="modal" id="approvalModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Supervisor or Manager Approval Required</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php echo isset($invalidCredentials) ? '<div class="alert alert-danger">Invalid username or password</div>' : "" ?>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" placeholder="Username" name="username">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" placeholder="Password" name="password">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php } ?>
    </div>

    <?php
    require('../scripts.php') ?>
    <script>
        $("#addRow").click(function() {
            var html = '';
            html += '<div id="inputFormRow">';
            html += '<div class="input-group mb-3">';
            html += "<input type='text' name='flightAttendantID[]' class='form-control m-input' placeholder='Enter the staff ID for flight attendant' autocomplete='off'>";
            html += '<div class="input-group-append">';
            html += '<button id="removeRow" type="button" class="btn btn-danger">Remove</button>';
            html += '</div>';
            html += '</div>';
            $('#newRow').append(html);
        });

        // remove row
        $(document).on('click', '#removeRow', function() {
            $(this).closest('#inputFormRow').remove();
        });

        <?php
        if (isset($showApprovalModal)) {
            echo "$('#approvalModal').modal('show');";
        }
        $connection->close();
        ?>
    </script>
</body>

</html>