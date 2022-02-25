<?php
$emailInvalid = false;
if (isset($_POST['email'])) {
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        session_start();
        $_SESSION['contactEmail'] = $_POST['email'];
        header('Location: payment.php');
        exit();
    } else {
        $emailInvalid = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('head.php') ?>
    <title>Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="container-fluid">
    <?php
    require("header.php");

    for ($i = 1; $i <= count($_SESSION['guests']); $i++) {
        if (isset($_POST['guestDepartBaggage'][$i])) {
            $_SESSION['guests'][$i]['departFlight']['baggage'] = $_POST['guestDepartBaggage'][$i];
        }

        if (isset($_SESSION['returnFlight']) && isset($_POST['guestReturnBaggage'][$i])) {
            $_SESSION['guests'][$i]['returnFlight']['baggage'] = $_POST['guestReturnBaggage'][$i];
        }
    }

    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $departFlightScheduleId = $_SESSION['departFlight'];
    $departFlightInfo = $connection->query("SELECT flights.registration, flights.departure, flight_schedules.depart_dateTime FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$departFlightScheduleId'")->fetch_assoc();

    if (isset($_SESSION['returnFlight'])) {
        $returnFlightScheduleId = $_SESSION['returnFlight'];
        $returnFlightInfo = $connection->query("SELECT flights.registration, flights.departure, flight_schedules.depart_dateTime FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$returnFlightScheduleId'")->fetch_assoc();
    }

    $baggageSelections = [
        '0' => ['text' => 'No checked baggage', 'price' => 0],
        '20' => ['text' => '20 kg', 'price' => 50],
        '25' => ['text' => '25 kg', 'price' => 60],
        '30' => ['text' => '30 kg', 'price' => 110],
        '40' => ['text' => '40 kg', 'price' => 150],
    ];

    ?>

    <?php
    for ($i = 1; $i <= count($_SESSION['guests']); $i++) {
        $guestDetail = $_SESSION['guests'][$i];
        echo "<h5>Guest $i: " . $guestDetail['name'] . "</h5>";
    ?>
        <p>Date of Birth: <?php echo $guestDetail['dob'] ?></p>
        <p>Gender: <?php echo $guestDetail['gender'] ?></p>
        <div class="row mb-5">
            <div class="col-2">
                <h6 class="mt-2">Departing Flight (<?php echo $departFlightInfo['registration']; ?>)</h6>
                <p>Departing Location: <?php echo $departFlightInfo['departure'] ?></p>
                <p>Departing Time: <?php echo $departFlightInfo['depart_dateTime'] ?></p>
                <p>Seat: <?php echo $guestDetail['departFlight']['seat'] ?></p>
                <p>Baggage: <?php echo $baggageSelections[$guestDetail['departFlight']['baggage']]['text'] ?></p>
            </div>
            <?php

            if (isset($_SESSION['returnFlight'])) {
            ?>
                <div class="col-2">
                    <h6 class="mt-2">Return Flight (<?php echo $returnFlightInfo['registration']; ?>)</h6>
                    <p>Departing Location: <?php echo $returnFlightInfo['departure'] ?></p>
                    <p>Departing Time: <?php echo $returnFlightInfo['depart_dateTime'] ?></p>
                    <p>Seat: <?php echo $guestDetail['returnFlight']['seat'] ?></p>
                    <p>Baggage: <?php echo $baggageSelections[$guestDetail['returnFlight']['baggage']]['text'] ?></p>
                </div>
            <?php
            }
            ?>
        </div>

    <?php
    }
    $guestNum = count($_SESSION['guests']);
    $seats = [
        'departFlight' => array(0, 0, 0),
        'returnFlight' => array(0, 0, 0)
    ];

    $baggages = [
        'departFlight' => [
            '0' => 0,
            '20' => 0,
            '25' => 0,
            '30' => 0,
            '40' => 0
        ],
        'returnFlight' => [
            '0' => 0,
            '20' => 0,
            '25' => 0,
            '30' => 0,
            '40' => 0
        ]
    ];

    // Get prices of tickets
    $ticketPricingResults = $connection->query("SELECT * FROM ticket_class");
    /*
    Ticket classes ID:
    First - 1
    Business - 2
    Economy - 3
    */
    while ($ticketClass = $ticketPricingResults->fetch_assoc()) {
        $ticketPricings[$ticketClass['id']] = $ticketClass['price'];
    }

    // Function to identify the class of the seat
    function identifySeatClass($seatNumber)
    {
        /*
        First class - Row 1 to 3 (Total seat in domestic = 3 x 6 = 18, Total seat in international = 3 x 7 = 21)
        Business class - Row 4 to 8 (Total seat in domestic = 5 x 6 = 30, Total seat in international = 5 x 7 = 35)
        Economy class - The rest
        */
        $row = substr($seatNumber, 0, -1);

        if ($row <= 3) {
            return 1; // First - 1
        } else if ($row <= 8) {
            return 2; // Business - 2
        } else {
            return 3; // Economy - 3
        }
    }

    foreach ($_SESSION['guests'] as $guestDetail) {
        $seats['departFlight'][identifySeatClass($guestDetail['departFlight']['seat']) - 1]++;
        $baggages['departFlight'][$guestDetail['departFlight']['baggage']]++;

        if (isset($_SESSION['returnFlight'])) {
            $seats['returnFlight'][identifySeatClass($guestDetail['returnFlight']['seat']) - 1]++;
            $baggages['returnFlight'][$guestDetail['returnFlight']['baggage']]++;
        }
    }

    $connection->close();
    ?>
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="container-fluid">
                        <tr>
                            <td>
                                <h6 class="mt-2">Departure Flight (<?php echo $departFlightInfo['registration']; ?>)</h6>
                            </td>
                        </tr>
                        <?php
                        $totalPrice = 0;
                        // Calculate price for each component in departing flight and add to total price
                        if ($seats['departFlight'][0] > 0) {
                            $price = $ticketPricings[1] * $seats['departFlight'][0];
                            $totalPrice += $price;
                            echo "<tr>";
                            echo "<td>" . $seats['departFlight'][0] . " x First Class Seat</td>";
                            echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                            echo "</tr>";
                        }
                        if ($seats['departFlight'][1] > 0) {
                            $price = $ticketPricings[2] * $seats['departFlight'][1];
                            $totalPrice += $price;
                            echo "<tr>";
                            echo "<td>" . $seats['departFlight'][1] . " x Business Class Seat</td>";
                            echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                            echo "</tr>";
                        }
                        if ($seats['departFlight'][2] > 0) {
                            $price = $ticketPricings[3] * $seats['departFlight'][2];
                            $totalPrice += $price;
                            echo "<tr>";
                            echo "<td>" . $seats['departFlight'][2] . " x Economy Class Seat</td>";
                            echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                            echo "</tr>";
                        }
                        foreach ($baggages['departFlight'] as $baggageKey => $quantity) {
                            if ($quantity > 0 && $baggageKey != 0) {
                                $price = $baggageSelections[$baggageKey]['price'] * $quantity;
                                $totalPrice += $price;
                                echo "<tr>";
                                echo "<td>$quantity x " . $baggageSelections[$baggageKey]['text'] . " baggage</td>";
                                echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </table>
                    <?php
                    // Calculate and display prices for return flight if there is a return flight
                    if (isset($_SESSION['returnFlight'])) { ?>
                        <table class="container-fluid">
                            <tr>
                                <td>
                                    <h6 class="mt-2">Return Flight (<?php echo $returnFlightInfo['registration']; ?>)</h6>
                                </td>
                            </tr>
                            <?php
                            // Calculate price for each component in return flight and add to total price
                            if ($seats['returnFlight'][0] > 0) {
                                $price = $ticketPricings[1] * $seats['returnFlight'][0];
                                $totalPrice += $price;
                                echo "<tr>";
                                echo "<td>" . $seats['returnFlight'][0] . " x First Class Seat</td>";
                                echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                                echo "</tr>";
                            }
                            if ($seats['returnFlight'][1] > 0) {
                                $price = $ticketPricings[2] * $seats['returnFlight'][1];
                                $totalPrice += $price;
                                echo "<tr>";
                                echo "<td>" . $seats['returnFlight'][1] . " x Business Class Seat</td>";
                                echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                                echo "</tr>";
                            }
                            if ($seats['returnFlight'][2] > 0) {
                                $price = $ticketPricings[3] * $seats['returnFlight'][2];
                                $totalPrice += $price;
                                echo "<tr>";
                                echo "<td>" . $seats['returnFlight'][2] . " x Economy Class Seat</td>";
                                echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                                echo "</tr>";
                            }
                            foreach ($baggages['returnFlight'] as $baggageKey => $quantity) {
                                if ($quantity > 0 && $baggageKey != 0) {
                                    $price = $baggageSelections[$baggageKey]['price'] * $quantity;
                                    $totalPrice += $price;
                                    echo "<tr>";
                                    echo "<td>$quantity x " . $baggageSelections[$baggageKey]['text'] . " baggage</td>";
                                    echo "<td class='text-right'>" . number_format($price, 2) . " MYR</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </table>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <h5>Total Price: <?php echo number_format($totalPrice, 2); ?> MYR</h5>
                </div>
            </div>
        </div>
    </div>
    <h3 style="cursor: pointer;" data-toggle="modal" data-target="#bookingDetailsModal">Total Price: <?php echo number_format($totalPrice, 2); ?> MYR (Click to view details)</h3>

    <form class="mb-3 mt-4" action="confirmation.php" method="POST">
        <div class="form-group col-3 pl-0">
            <label for="email">Contact Email:</label>
            <!-- Show error if invalid and display previous data if there is previous data -->
            <input type="email" class="form-control <?php if ($emailInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST["email"] ?? ""; ?>" id="email" name="email">
            <?php if ($emailInvalid) echo '<div class="invalid-feedback">Invalid email.</div>'; ?>
        </div>
        <input type="submit" value="Proceed to purchase" class="btn btn-primary">
    </form>

    <?php require('scripts.php') ?>
</body>

</html>