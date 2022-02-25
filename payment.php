<?php


$accountCardNumberInvalid = false;
$monthInvalid = false;
$yearInvalid = false;
$CvvCvcInvalid = false;
$accountCardNameInvalid = false;

$accountCardNumber = $_POST['accountCardNumber'] ?? null;
$month = $_POST['month'] ?? null;
$year = $_POST['year'] ?? null;
$CvvCvc = $_POST['CvvCvc'] ?? null;
$accountCardName = $_POST['accountCardName'] ?? null;

$paymentMethod = $_GET['method'] ?? 1;

// Resume current session
session_start();
// Create connection with MySQL database
$connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Send POST request
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Input validation
    if (empty($accountCardNumber)) {
        $accountCardNumberInvalid = true;
    }

    if (($month) == "MM") {
        $monthInvalid = true;
    }

    if (($year) == "YYYY") {
        $yearInvalid = true;
    }

    if (empty($CvvCvc)) {
        $CvvCvcInvalid = true;
    }

    if (empty($accountCardName)) {
        $accountCardNameInvalid = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('head.php') ?>
    <title>Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="container-fluid">
    <?php
    require("header.php");

    // Set depart baggage to depart flight
    for ($i = 1; $i <= count($_SESSION['guests']); $i++) {
        if (isset($_POST['guestDepartBaggage'][$i])) {
            $_SESSION['guests'][$i]['departFlight']['baggage'] = $_POST['guestDepartBaggage'][$i];
        }

        if (isset($_SESSION['returnFlight']) && isset($_POST['guestReturnBaggage'][$i])) {
            $_SESSION['guests'][$i]['returnFlight']['baggage'] = $_POST['guestReturnBaggage'][$i];
        }
    }

    // Insert sesion variable depart flight into new variable
    $departFlightScheduleId = $_SESSION['departFlight'];
    // Fetch all values as an associative array
    $departFlightInfo = $connection->query("SELECT flights.registration, flights.departure, flight_schedules.depart_dateTime FROM flights INNER JOIN flight_schedules ON flights.id = flight_schedules.flight_no WHERE flight_schedules.id = '$departFlightScheduleId'")->fetch_assoc();

    // Fetch all values as an associative array
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
    $paymentSelections = [
        '1' => 'Credit/Debit Card',
        '2' => 'Online Banking'
    ];

    $months = [
        'MM',
        '01',
        '02',
        '03',
        '04',
        '05',
        '06',
        '07',
        '08',
        '09',
        '10',
        '11',
        '12'
    ];

    $years = [
        'YYYY',
        '2020',
        '2021',
        '2022',
        '2023',
        '2024',
        '2025',
        '2026',
        '2027',
        '2028',
        '2029',
        '2030',
        '2031',
    ];

    ?>

    <!-- Payment form -->
    <form action="payment.php" method="POST">
        <div class="row mb-5">
            <div class="col-2">
                <div class="form-group">
                    <select id="payment" class="form-control" name="paymentOption[<?php echo $i; ?>]" onchange="window.location = 'payment.php?method=' + this.value">
                        <?php
                        foreach ($paymentSelections as $value => $text) {
                            echo "<option " . ($paymentMethod == $value ? 'selected' : '') . " value='$value'>$text</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php if ($paymentMethod == 1) { ?>
                <div class="col-4">
                    <h5>Credit/Debit Card</h5>
                    <div class="input-group">
                        <input type="number" class="form-control <?php if ($accountCardNumberInvalid) echo "is-invalid"; ?>" placeholder="Card number" value="<?php echo $accountCardNumber ?? ""; ?>" id="accountCardNumber" name="accountCardNumber">
                        <div class="input-group-append">
                            <div class="input-group-text"><i class="far fa-credit-card"></i></div>
                        </div>
                        <?php if ($accountCardNumberInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                    </div>
                    <p>A processing fee may be added to your total fare after you've entered your card number</p>
                    <h6>Expiration Date</h6>
                    <div class="row">
                        <div class="form-group col-sm-3">
                            <select id="month" class="form-control <?php if ($monthInvalid) echo "is-invalid"; ?>" name="month">
                                <?php
                                foreach ($months as $text) {
                                    echo "<option " . ($month == $text ? 'selected' : '') . ">$text</option>";
                                }
                                ?>
                            </select>
                            <?php if ($monthInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                        </div>
                        <div class="form-group col-sm-3">
                            <select id="year" class="form-control <?php if ($yearInvalid) echo "is-invalid"; ?>" name="year">
                                <?php
                                foreach ($years as $text) {
                                    echo "<option " . ($year == $text ? 'selected' : '') . ">$text</option>";
                                }
                                ?>
                            </select>
                            <?php if ($yearInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                        </div>
                        <div class="form-group col-sm-3">
                            <input type="text" class="form-control <?php if ($CvvCvcInvalid) echo "is-invalid"; ?>" placeholder="CVV/CVC" value="<?php echo $CvvCvc ?? ""; ?>" id="CvvCvc" name="CvvCvc">
                            <?php if ($CvvCvcInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                        </div>
                        <i class="far fa-credit-card fa-2x"></i>
                    </div>
                    <div>
                        <input type="text" class="form-control <?php if ($accountCardNameInvalid) echo "is-invalid"; ?>" placeholder="Name on card" value="<?php echo $accountCardName ?? ""; ?>" id="accountCardName" name="accountCardName">
                        <?php if ($accountCardNameInvalid) echo "<div class='invalid-feedback'>This field is required.</div>"; ?>
                    </div>
                    <br><br>
                    <div class="float-right">
                        <input type="submit" value="Confirm Payment" class="btn btn-primary">
                    </div>
                </div>
            <?php } else { ?>
                <div class="col-4">
                    <h5>Online Banking</h5>
                    <div class="card" style="width: 18 rem;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="Maybank" name="bank" value="option1" checked>
                                <label class="form-check-label" for="exampleRadios1">Maybank2U</label>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="width: 18 rem;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="CIMB" name="bank" value="option2">
                                <label class="form-check-label" for="exampleRadios1">CIMB Clicks</label>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="width: 18 rem;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="PublicBank" name="bank" value="option3">
                                <label class="form-check-label" for="exampleRadios1">Public Bank</label>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="width: 18 rem;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="FPX" name="bank" value="option4">
                                <label class="form-check-label" for="exampleRadios1">FPX (Other Banks)</label>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <div class="float-right">
                        <input type="submit" value="Confirm Payment" class="btn btn-primary">
                    </div>
                </div>
            <?php } ?>
        </div>
    </form>

    </div>
    <?php
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
    <div class="col-2">
        <p style="cursor: pointer;" data-toggle="modal" data-target="#bookingDetailsModal">Total Price: <?php echo number_format($totalPrice, 2); ?> MYR <br> (Click to view details)</p>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (!$accountCardNumberInvalid && !$monthInvalid && !$yearInvalid && !$CvvCvcInvalid && !$accountCardNameInvalid) {
            if ($paymentMethod = 1) {
                $_SESSION['bookings'] = [
                    'accountCardNumber' => $_POST['accountCardNumber'],
                    'month' => $_POST['month'],
                    'year' => $_POST['year'],
                    'CvvCvc' => $_POST['CvvCvc'],
                    'accountCardName' => $_POST['accountCardName']
                ];
            }
            $paymentType = $paymentMethod == 1 ? 'CreditCard' : 'OnlineBanking';
            $transactionNumber = "T" .  mt_rand(10000, 99999);
            $email = $_SESSION['contactEmail'];
            $customerNumber = $_SESSION['customerId'] ?? "NULL";
            if ($connection->query("INSERT INTO bookings (payment_dateTime, transaction_no, account_card_no, amount, payment_type, remark, email, cust_no) VALUES (CURRENT_TIMESTAMP(), '$transactionNumber', '$accountCardNumber', $totalPrice, '$paymentType', '', '$email', $customerNumber)") === true) {
                $bookingNumber = $connection->insert_id;
                $departScheduleNumber = $_SESSION['departFlight'];
                if (isset($_SESSION['returnFlight'])) {
                    $returnScheduleNumber = $_SESSION['returnFlight'];
                }
                foreach ($_SESSION['guests'] as $guest) {
                    $name = $guest['name'];
                    $dob = date('Y-m-d', strtotime($guest['dob']));
                    $gender = $guest['gender'];
                    $departSeatNumber = $guest['departFlight']['seat'];
                    $departClass = identifySeatClass($departSeatNumber);
                    $departBaggage = $guest['departFlight']['baggage'];
                    $departPrice = $ticketPricings[$departClass] + $baggageSelections[$departBaggage]['price'];
                    $connection->query("INSERT INTO flight_tickets (booking_no, schedule_no, class, seat_no, price, passenger_name, gender, date_of_birth, baggage_limit) VALUES ('$bookingNumber', '$departScheduleNumber', '$departClass', '$departSeatNumber', '$departPrice', '$name', '$gender', '$dob', '$departBaggage')");

                    if (isset($_SESSION['returnFlight'])) {
                        $returnSeatNumber = $guest['returnFlight']['seat'];
                        $returnClass = identifySeatClass($returnSeatNumber);
                        $returnBaggage = $guest['returnFlight']['baggage'];
                        $returnPrice = $ticketPricings[$returnClass] + $baggageSelections[$returnBaggage]['price'];
                        $connection->query("INSERT INTO flight_tickets (booking_no, schedule_no, class, seat_no, price, passenger_name, gender, date_of_birth, baggage_limit) VALUES ('$bookingNumber', '$returnScheduleNumber', '$returnClass', '$returnSeatNumber', '$returnPrice', '$name', '$gender', '$dob', '$returnBaggage')");
                    }
                }

                // Successfully added the record, redirecting to the record's page
                echo '<meta http-equiv="refresh" content="0;url=payment-confirmed.php?id=' . $bookingNumber . '">';
            } else {
                $error = true;
            }
        }
    }
    $connection->close();
    ?>
    <?php require('scripts.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-2JBCbWoMJPH+Uj7Wq5OLub8E5edWHlTM4ar/YJkZh3plwB2INhhOC3eDoqHm1Za/ZOSksrLlURLoyXVdfQXqwg==" crossorigin="anonymous"></script>