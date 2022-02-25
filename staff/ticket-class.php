<?php
require('validateLogin.php');

$connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$ticketClassInvalid = false;
$ticketPriceInvalid = false;

$ticketClass = $_POST['ticketClass'] ?? null;
$ticketPrice = $_POST['ticketPrice'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (($ticketClass) == 0) {
        $ticketClassInvalid = true;
    }

    if (empty($ticketPrice)) {
        $ticketPriceInvalid = "This field is required.";
    } else if ($ticketPrice < 0) {
        $ticketPriceInvalid = "Invalid ticket price.";
    }

    if (!$ticketClassInvalid && !$ticketPriceInvalid) {
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
            if ($connection->query("UPDATE ticket_class SET price=$ticketPrice WHERE id=" . $ticketClass) === true) {
                $successful = true;
            } else {
                echo $connection->error;
                $error = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>AirAsia Staff Portal | Flight Ticket Class</title>
</head>

<body>
    <?php
    $page = 'ticketSales';
    require('navbar.php');

    $class = [
        '0' => "Select",
        '1' => "First Class",
        '2' => "Business Class",
        '3' => "Economy Class"
    ]
    ?>
    <div class="container-fluid p-3">
        <h3>Update Flight Ticket Class</h3>
        <?php echo isset($error) ? '<div class="alert alert-danger col-6">An error has occured. Please try again.</div>' : "" ?>
        <?php echo isset($successful) ? '<div class="alert alert-success col-6">Update successful.</div>' : "" ?>
        <form action="ticket-class.php" method="POST">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="registration">Flight Ticket Class</label>
                    <select id="class" class="form-control <?php if ($ticketClassInvalid) echo "is-invalid"; ?>" name="ticketClass">
                        <?php
                        foreach ($class as $value => $text) {
                            echo "<option " . ($ticketClass == $value ? 'selected' : '') . " value='" . $value . "'>$text</option>";
                        }
                        ?>
                    </select>
                    <!-- Display previous data if there is previous data -->
                    <?php if ($ticketClassInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                </div>
                <div class="form-group col-3">
                    <label for="registration">Price (MYR)</label>
                    <!-- Display previous data if there is previous data -->
                    <input type="number" class="form-control <?php if ($ticketPriceInvalid) echo "is-invalid"; ?>" value="<?php echo $ticketPrice ?? ""; ?>" id="ticketPrice" name="ticketPrice">
                    <?php if ($ticketPriceInvalid) echo "<div class='invalid-feedback'>$ticketPriceInvalid</div>"; ?>
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