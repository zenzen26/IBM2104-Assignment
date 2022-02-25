<?php
require('validateLogin.php');

$connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$registrationInvalid = false;
$arrivalLocationInvalid = false;
$estimatedDurationInvalid = false;
$airplaneNumberInvalid = false;
$airplaneModelInvalid = false;
$capacityInvalid = false;

$registration = $_POST['registration'] ?? null;
$departLocation = $_POST['departLocation'] ?? null;
$arrivalLocation = $_POST['arrivalLocation'] ?? null;
$estimatedDuration = $_POST['estimatedDuration'] ?? null;
$flightType = $_POST['flightType'] ?? null;
$airplaneNumber = $_POST['airplaneNumber'] ?? null;
$airplaneModel = $_POST['airplaneModel'] ?? null;
$capacity = $_POST['capacity'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (empty($registration)) {
        $registrationInvalid = true;
    }

    // Arrival location cannot be the same as depart location
    if ($departLocation == $arrivalLocation) {
        $arrivalLocationInvalid = true;
    }

    if (empty($estimatedDuration) || $estimatedDuration < 1) {
        $estimatedDurationInvalid = true;
    }

    if (empty($airplaneNumber)) {
        $airplaneNumberInvalid = true;
    }

    if (empty($airplaneModel)) {
        $airplaneModelInvalid = true;
    }

    if (empty($capacity) || $capacity < 1) {
        $capacityInvalid = true;
    }

    if (!$registrationInvalid && !$arrivalLocationInvalid && !$estimatedDurationInvalid && !$airplaneNumberInvalid && !$airplaneModelInvalid && !$capacityInvalid) {
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
            if (isset($_GET['id'])) {
                // ID is provided, this is a update
                if ($connection->query("UPDATE flights SET registration='$registration', departure='$departLocation', arrival='$arrivalLocation', est_duration=$estimatedDuration, type='$flightType', airplane_no=$airplaneNumber, airplane_model='$airplaneModel', capacity=$capacity WHERE id=" . $_GET['id']) === true) {
                    $successful = true;
                } else {
                    $error = true;
                }
            } else {
                // ID is not provided, this is a new flight
                if ($connection->query("INSERT INTO flights (registration, departure, arrival, est_duration, type, airplane_no, airplane_model, capacity) VALUES ('$registration', '$departLocation', '$arrivalLocation', $estimatedDuration, '$flightType', $airplaneNumber, '$airplaneModel', $capacity)") === true) {
                    // Successfully added the record, redirecting to the record's page
                    header('Location: flight.php?id=' . $connection->insert_id);
                    $connection->close();
                    exit();
                } else {
                    $error = true;
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>AirAsia Staff Portal | Flight</title>
</head>

<body>
    <?php
    $page = 'flights';
    require('navbar.php');

    if (isset($_GET['id'])) {
        $result = $connection->query("SELECT * FROM flights WHERE id = '" . $_GET['id'] . "';")->fetch_assoc();
        $registration = $registration ?? $result['registration'];
        $departLocation = $departLocation ?? $result['departure'];
        $arrivalLocation = $arrivalLocation ?? $result['arrival'];
        $estimatedDuration = $estimatedDuration ?? $result['est_duration'];
        $flightType = $flightType ?? $result['type'];
        $airplaneNumber = $airplaneNumber ?? $result['airplane_no'];
        $airplaneModel = $airplaneModel ?? $result['airplane_model'];
        $capacity = $capacity ?? $result['capacity'];
    }

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

    $flightTypes = [
        "Domestic",
        "International"
    ]
    ?>
    <div class="container-fluid p-3">
        <h3><?php echo isset($_GET['id']) ? "Update" : "Add" ?> Flight</h3>
        <?php echo isset($error) ? '<div class="alert alert-danger col-6">An error has occured. Please try again.</div>' : "" ?>
        <?php echo isset($successful) ? '<div class="alert alert-success col-6">Update successful.</div>' : "" ?>
        <form action="flight.php<?php echo isset($_GET['id']) ? "?id=" . $_GET['id'] : "" ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="registration">Registration</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="text" class="form-control <?php if ($registrationInvalid) echo "is-invalid"; ?>" value="<?php echo $registration ?? ""; ?>" id="registration" name="registration">
                    <?php if ($registrationInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="departLocation">Departure Location</label>
                    <select id="departLocation" class="form-control" name="departLocation">
                        <?php
                        foreach ($airports as $airport) {
                            echo "<option " . (($departLocation == $airport) ? "selected" : "") . ">$airport</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-3">
                    <label for="arrivalLocation">Arrival Location</label>
                    <select id="arrivalLocation" class="form-control <?php if ($arrivalLocationInvalid) echo "is-invalid"; ?>" name="arrivalLocation">
                        <?php
                        foreach ($airports as $airport) {
                            echo "<option " . (($arrivalLocation == $airport) ? "selected" : "") . ">$airport</option>";
                        }
                        ?>
                    </select>
                    <?php if ($arrivalLocationInvalid) echo '<div class="invalid-feedback">Destination cannot be the same as the origin.</div>'; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="estimatedDuration">Estimated Duration (In Minutes)</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="number" class="form-control <?php if ($estimatedDurationInvalid) echo "is-invalid"; ?>" value="<?php echo $estimatedDuration ?? ""; ?>" id="estimatedDuration" name="estimatedDuration">
                    <?php if ($estimatedDurationInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
                <div class="form-group col-3">
                    <label for="flightType">Flight Type</label>
                    <select id="flightType" class="form-control" name="flightType">
                        <?php
                        foreach ($flightTypes as $type) {
                            echo "<option " . (($flightType == $type) ? "selected" : "") . ">$type</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="airplaneNumber">Airplane Number</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="number" class="form-control <?php if ($airplaneNumberInvalid) echo "is-invalid"; ?>" value="<?php echo $airplaneNumber ?? ""; ?>" id="airplaneNumber" name="airplaneNumber">
                    <?php if ($airplaneNumberInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
                <div class="form-group col-3">
                    <label for="airplaneModel">Airplane Model</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="text" class="form-control <?php if ($airplaneModelInvalid) echo "is-invalid"; ?>" value="<?php echo $airplaneModel ?? ""; ?>" id="airplaneModel" name="airplaneModel">
                    <?php if ($airplaneModelInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="capacity">Capacity</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="number" class="form-control <?php if ($capacityInvalid) echo "is-invalid"; ?>" value="<?php echo $capacity ?? ""; ?>" id="capacity" name="capacity">
                    <?php if ($capacityInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <?php if (isset($showApprovalModal)) { ?>
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
            <?php }
            $connection->close();
            ?>
        </form>
    </div>

    <?php require('../scripts.php') ?>
    <script type="text/javascript">
        <?php
        if (isset($showApprovalModal)) {
            echo "$('#approvalModal').modal('show');";
        }
        ?>
    </script>
</body>

</html>