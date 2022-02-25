<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
  <?php require('head.php');
  require('header.php'); ?>
  <title>Check In</title>
</head>

<body>

  <div class="container">


    <?php
    //Connection to database establish
    $connection = new mysqli("localhost", "admin", null, "ibm2104_assignment");
    if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
    }

    if (isset($_SESSION['customerId'])) {
      $cust_no = $_SESSION['customerId'];
      echo "<h3>Booking Number | All</h3>";
      echo "<p>Choose a booking number to perform online check in</p>";

      //Query to search for booking number
      $query = "SELECT DISTINCT bookings.id, bookings.email, flights.departure, flights.arrival, flight_schedules.depart_dateTime, flight_schedules.arrive_dateTime FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no INNER JOIN flight_tickets ON flight_schedules.id = flight_tickets.schedule_no INNER JOIN bookings ON flight_tickets.booking_no = bookings.id WHERE bookings.cust_no =$cust_no";
      $result = mysqli_query($connection, $query);

      if ($result->num_rows > 0) {

        echo "<table class='table table-hover'>";
        echo "<thead class='thead-light'>";
        echo "<tr>";
        echo "<th>Booking Number</th>";
        echo "<th>Departure Location</th>";
        echo "<th>Arrival Location</th>";
        echo "<th>Depart Date & Time</th>";
        echo "<th>Arrival Date & Time</th>";
        echo "</tr>";
        echo "</thead>";

        //Check if the passenger can do online check in already or not
          while($row = mysqli_fetch_array($result)){
            //Calculate eligible check in time
            date_default_timezone_set("Singapore");
            $currentTime = date("Y/m/d h:i:sa");
            $timestamp = strtotime($row['depart_dateTime']);
            $time = $timestamp - (24 * 60 * 60);
            $eligibleTime = date("Y-m-d H:i:s", $time);

            if (strtotime($currentTime) >= strtotime($eligibleTime) && strtotime($currentTime) <= $timestamp) {
            echo '<tr onclick="window.location = \'' . 'check-in-2.php?bookingNumber=' . $row['id'] . '&email=' . $row['email'] . '\';">';
            echo "<td col>" . $row['id'] . "</td>";
            echo "<td col>" . $row['departure'] . "</td>";
            echo "<td col>" . $row['arrival'] . "</td>";
            echo "<td col>" . $row['depart_dateTime'] . "</td>";
            echo "<td col>" . $row['arrive_dateTime'] . "</td>";
            echo "</tr>";
            }
          }

        echo "</table>";
     } else {
       echo "<p>You have no bookings to check in</p>";
     }
    } else {
    ?>
      <h3>Booking Number | Search</h3>
      <p>Enter a booking number and surname to perform online check in</p>
      <form action="check-in-2.php" method="get">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="bookingNumber">Booking Number</label>
            <input type="text" class="form-control" name="bookingNumber" required>
          </div>

          <div class="form-group col-md-5">
            <label for="surname">Email</label>
            <input type="text" class="form-control" name="email" required>
          </div>
        </div>

        <input type="submit" class="btn btn-primary" value="Search">

      </form>
    <?php
    }
    $connection->close();
    ?>
  </div>

</body>

</html>