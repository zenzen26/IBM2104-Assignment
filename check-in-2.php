<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
  <?php require('head.php');
  require('header.php'); ?>
  <title>Check In</title>
  <style>
    tr {
      cursor: pointer;
    }
  </style>
</head>

<body>

  <?php
  //Connection to database establish
  $connection = new mysqli("localhost", "admin", null, "ibm2104_assignment");
  if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
  }

  //Request check in time
  date_default_timezone_set("Singapore");
  $currentTime = date("Y/m/d h:i:sa");

  $bookingNum = $_GET['bookingNumber'];
  $email = $_GET['email'];

  //Query to check for booking number
  $query = "SELECT * FROM bookings WHERE id = '$bookingNum' AND email = '$email'";
  //Result of query
  $result = mysqli_query($connection, $query);
  if ($result->num_rows > 0) {

    //Query to retrieve info
    $query = "SELECT flight_tickets.id, flight_tickets.schedule_no, flights.departure, flights.arrival, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no INNER JOIN flight_tickets ON flight_schedules.id = flight_tickets.schedule_no INNER JOIN bookings ON flight_tickets.booking_no = bookings.id WHERE bookings.id =$bookingNum";
    $result = mysqli_query($connection, $query);
    $resultDetails = mysqli_fetch_array($result);
    $departDateTime = $resultDetails['depart_dateTime'];
    $departureLocation = $resultDetails['departure'];
    $arrivalLocation = $resultDetails['arrival'];
    $scheduleID = $resultDetails['schedule_no'];

    //Calculate eligible check in time
    $timestamp = strtotime($departDateTime);
    $time = $timestamp - (24 * 60 * 60);
    $eligibleTime = date("Y-m-d H:i:s", $time);

    //Check if the passenger can do online check in already or not
    if (strtotime($currentTime) >= strtotime($eligibleTime) && strtotime($currentTime) <= $timestamp) {

  ?>

      <div class=container>
        <h3>Check-In</h3>
        <p>Please enter the details of date of birth, country of residence, passport number, passport expiry date and passport issuing country to confirm the check in for each passenger. Kindly fill the details by clicking the row of each passenger in the list.</p>
        <table class="table">
          <thead class="thead-light">
            <tr>
              <th scope="col"><?php echo $departureLocation . " to " . $arrivalLocation; ?></th>
              <th scope="col" style="text-align: right;"><?php echo $departDateTime; ?></th>
            </tr>
          </thead>

          <?php

          //Display customer details
          $passengerQuery = "SELECT flight_tickets.* FROM flight_tickets INNER JOIN flight_schedules ON flight_tickets.schedule_no = flight_schedules.id WHERE booking_no = $bookingNum";
          $resultPassenger = mysqli_query($connection, $passengerQuery);
          while ($row = mysqli_fetch_array($resultPassenger)) {
            if ($row['status'] == "Pending") {
              $checkedStatement = "<strong>Not Checked In</strong>";
            } else {
              $checkedStatement = "<strong>Checked In</strong>";
            }
            echo "<tr " . (($row['status'] == "Pending") ? "data-toggle='modal' data-target='#checkInDetailsModal'" : "") . " data-id='" . $row['id'] . "' >";
            echo "<td>" . $row['passenger_name'] . "</td>";
            echo "<td scope='col' style='text-align: right;'>" . "$checkedStatement " . "</td>";
            echo "</tr>";
          }
          ?>
        </table>
        <div class="row justify-content-between">
          <div class="col-auto">
            <a href="check-in-1.php"><button class="btn btn-secondary">Back</button></a>
          </div>
          <div class="col-auto">
           <a href="check-in-1.php"><button class= "btn btn-primary">Confirm</button></a>
          </div>
        </div>
      </div>

  <?php
    } else {
      echo "<div class='alert alert-danger' role='alert'><strong>Oops!</strong> There is no check in available for your flight as of now! <a href='check-in-1.php' class='alert-link'>Click here to go back</a></div>";
    }
  } else {
    echo "<div class='alert alert-danger' role='alert'><strong>Search failed!</strong> Please check if you have entered the correct booking number and surname! <a href='check-in-1.php' class='alert-link'>Click here to go back</a></div>";
  }

  //Check and submit the passenger check in
  if (isset($_GET['ticketID']) && $_GET['ticketID'] !== "" && isset($_GET['dob']) && $_GET['dob'] !== "" && isset($_GET['countryResidence']) && $_GET['countryResidence'] !== "" && isset($_GET['passportNumber']) && $_GET['passportNumber'] !== "" && isset($_GET['pExpiry']) && $_GET['pExpiry'] !== "" && isset($_GET['passportCountry']) && $_GET['passportCountry'] !== "") {
    $ticketID = $_GET['ticketID'];
    $dob = strtotime($_GET['dob']);
    $countryResidence = $_GET['countryResidence'];
    $passportNum = $_GET['passportNumber'];
    $passportExpiry = $_GET['pExpiry'];
    $passportCountry = $_GET['passportCountry'];

    $checkDOB = "SELECT date_of_birth FROM flight_tickets WHERE id = $ticketID";
    $dobResult = mysqli_query($connection, $checkDOB);
    $passengerDOB = mysqli_fetch_array($dobResult);
    if ($dob == strtotime($passengerDOB['date_of_birth'])) {
      if (!empty($dobResult) && $dobResult->num_rows > 0) {
        $updateQuery = "UPDATE flight_tickets SET ic_passport = '$passportNum', passport_country = '$passportCountry', residence_country = '$countryResidence', status = 'CheckedIn' WHERE id = $ticketID";
        if (mysqli_query($connection, $updateQuery) === TRUE)
          echo '<meta http-equiv="refresh" content="0;url=check-in-2.php?bookingNumber=' . $bookingNum . '&email=' . $email . '">';
      }
    }
  }
  ?>

  <!-- Modal UI -->
  <div class="modal fade" id="checkInDetailsModal" tabindex="-1" role="dialog" aria-labelledby="checkInDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="checkInDetailsModalLabel"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="checkInDetails">
          <form action="check-in-2.php" method="get" id="form1">
            <div class="form-row">
              <div class="form-group col">
                <label for="dob">Date of Birth</label>
                <div class="input-group date" id="dobDatePicker" data-target-input="nearest">
                  <input type="text" class="form-control datetimepicker-input" data-target="#dobDatePicker" id="dob" name="dob" required />
                  <div class="input-group-append" data-target="#dobDatePicker" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                  </div>
                </div>
              </div>

              <div class="form-group col">
                <label for="passenger-country" class="col-form-label">Country of Residence</label>
                <input type="text" class="form-control" id="passenger-country" name="countryResidence">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col">
                <label for="passenger-pNum" class="col-form-label">IC / Passport Number</label>
                <input type="text" class="form-control" id="passenger-pNum" name="passportNumber">
                <small id="passportNumberHelp" class="form-text text-muted">Enter ic number if you are flying domestic flight and passport number for international flight.</small>
              </div>

              <div class="form-group col">
                <label for="pExpiry">IC / Passport Expiry Date</label>
                <div class="input-group date" id="pDatePicker" data-target-input="nearest">
                  <input type="text" class="form-control datetimepicker-input" data-target="#pDatePicker" id="pExpiry" name="pExpiry">
                  <div class="input-group-append" data-target="#pDatePicker" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col">
                <label for="passenger-pDate" class="col-form-label">Passport Issuing Country</label>
                <input type="text" class="form-control required" id="passenger-pDate" name="passportCountry">
              </div>
            </div>
            <input type="hidden" name="ticketID">
            <input type="hidden" name="bookingNumber" value=<?php echo $bookingNum ?>>
            <input type="hidden" name="email" value=<?php echo $email ?>>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <a href="#" id="submit" class="btn btn-success success">Submit</a>
        </div>
      </div>
    </div>
  </div>


  <?php
  $connection->close();
  require('scripts.php')
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>
  <script type="text/javascript">
    $(function() {
      $('#dobDatePicker').datetimepicker({
        format: 'YYYY-MM-DD',
      });
      $('#pDatePicker').datetimepicker({
        format: 'YYYY-MM-DD',
      });
    });

    $(function() {
      $('#checkInDetailsModal').modal({
        keyboard: true,
        backdrop: "static",
        show: false,

      }).on('show.bs.modal', function() {
        var getIdFromRow = $(event.target).closest('tr').data('id');
        console.log(getIdFromRow);
        $('input[name ="ticketID"]').val(getIdFromRow);
      });
      $('#submit').click(function() {
        $('#form1').submit();
      });
    });
  </script>
</body>

</html>