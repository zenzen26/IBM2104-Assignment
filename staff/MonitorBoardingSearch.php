<?php require('validateLogin.php'); ?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>Boarding Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="d-flex flex-column h-100">
    <?php
    require('navbar.php');

    //Arrays
    $status = [
        "Any Status", "Scheduled", "Arrived", "Cancelled"
    ];
    ?>

    <div class="container">
        <div class="row my-4">
            <h3 class="col">Monitoring of Boarding | Search</h3>
        </div>
        <form action="MonitorBoardingFlights.php" method="GET">
            <div class="row">
                <div class="col">
                    <div class="input-group">
                        <label for="flightNumber">Flight Schedule Number:</label>
                        <div class="input-group">
                            <input name="flightNumber" type="text" class="form-control" aria-label="Flight ID">
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="flightStatus">Flight Status:</label>
                        <select name="flightStatus" class="form-control" id="flightStatus">
                            <?php foreach ($status as $value) {
                                echo "<option>" . $value . "</option>";
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="departureDate">Departure Date:</label>
                        <div class="input-group date" id="departureDate" data-target-input="nearest">
                            <input name="departureDate" id="departureDate" type="text" class="form-control datetimepicker-input" data-target="departureDate" />
                            <div class="input-group-append" data-target="#departureDate" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="arrivalDate">Arrival Date:</label>
                        <div class="input-group date" id="arrivalDate" data-target-input="nearest">
                            <input name="arrivalDate" id="arrivalDate" type="text" class="form-control datetimepicker-input" data-target="arrivalDate" />
                            <div class="input-group-append" data-target="#arrivalDate" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row my-4">
                <div class="button col offset-12">
                    <button class="btn btn-danger" type="submit">Search</button>
                </div>
            </div>
        </form>
    </div>

    <?php require('../scripts.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(function() {
            $('#departureDate').datetimepicker({
                format: 'DD-MM-YYYY',
            });
            $('#arrivalDate').datetimepicker({
                format: 'DD-MM-YYYY',
            });
        });
    </script>
</body>

</html>