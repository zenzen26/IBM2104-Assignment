<?php
session_start();

// Validate and store in session before proceeding
if (isset($_POST['guests'])) {
    $valid = true;
    foreach ($_POST['guests'] as $guest) {
        if (empty($guest['name']) || empty($guest['dob'])) {
            $valid = false;
        }
    }

    if ($valid) {
        // If the input is valid, store in session and redirect to next page
        $_SESSION['guests'] = $_POST['guests'];
        header('Location: seating.php');
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('head.php') ?>
    <title>Guest Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="container-fluid">
    <?php
    require("header.php");

    $guestNum = $_POST['guestNum'] ?? count($_POST['guests']);

    if (isset($_POST['departFlight'])) {
        $_SESSION['departFlight'] = $_POST['departFlight'];
    }
    if (isset($_POST['returnFlight']) && !empty($_POST['returnFlight'])) {
        $_SESSION['returnFlight'] =  $_POST['returnFlight'];
    }

    ?>

    <h3 class="mb-4">Guest Information</h3>

    <!-- Submit to own page to validate input first -->
    <form action="guestInfo.php" method="POST">
        <?php
        for ($i = 1; $i <= $guestNum; $i++) {
            $nameInvalid = isset($_POST["guests"][$i]['name']) && empty($_POST["guests"][$i]['name']);
            $dobInvalid = isset($_POST["guests"][$i]['dob']) && empty($_POST["guests"][$i]['dob']);
        ?>
            <h5>Guest <?php echo $i; ?></h5>
            <div class="form-row">
                <div class="form-group col-4">
                    <label for="name">Name</label>
                    <!-- Show error if invalid and display previous data if there is previous data -->
                    <input type="text" class="form-control <?php if ($nameInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST["guests"][$i]['name'] ?? ""; ?>" id="name" name="guests[<?php echo $i; ?>][name]">
                    <?php if ($nameInvalid) echo '<div class="invalid-feedback">This field is required.</div>'; ?>
                </div>
            </div>
            <div class="form-row mb-4">
                <div class="form-group col-2">
                    <label for="dob">Date of Birth</label>
                    <div class="input-group date" id="dob<?php echo $i; ?>Picker" data-target-input="nearest">
                        <!-- Show error if invalid and display previous data if there is previous data -->
                        <input type="text" class="form-control datetimepicker-input <?php if ($dobInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST["guests"][$i]['dob'] ?? ""; ?>" data-target="#dob<?php echo $i; ?>Picker" id="dob" name="guests[<?php echo $i; ?>][dob]" />
                        <div class="input-group-append" data-target="#dob<?php echo $i; ?>Picker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                        </div>
                        <?php if ($dobInvalid) echo '<div class="invalid-feedback">This field is required.</div>'; ?>
                    </div>
                </div>
                <div class="form-group col-2">
                    <label for="gender">Gender</label>
                    <select id="gender" class="form-control" name="guests[<?php echo $i; ?>][gender]">
                        <!-- Display previous data if there is previous data -->
                        <option <?php echo isset($_POST["guests"][$i]['gender']) && $_POST["guests"][$i]['gender'] == "Male" ? "selected" : ""; ?>>Male</option>
                        <option <?php echo isset($_POST["guests"][$i]['gender']) && $_POST["guests"][$i]['gender'] == "Female" ? "selected" : ""; ?>>Female</option>
                    </select>
                </div>
            </div>
        <?php
        }
        ?>
        <a href="flights.php"><button type="button" class="btn btn-secondary">Back</button></a>
        <button type="submit" class="btn btn-primary">Next</button>
    </form>


    <?php require('scripts.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>
    <script type="text/javascript">
        <?php
        for ($i = 1; $i <= $guestNum; $i++) {
        ?>
            $(function() {
                $('#dob<?php echo $i; ?>Picker').datetimepicker({
                    format: 'DD-MM-YYYY',
                    maxDate: new Date().toISOString().slice(0, 10),
                    useCurrent: false
                });
            });
        <?php
        }
        ?>
    </script>
</body>

</html>