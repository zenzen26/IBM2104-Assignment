<?php
$emailInvalid = false;
if (isset($_POST['email'])) {
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
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
    <title>Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-PMjWzHVtwxdq7m7GIxBot5vdxUY+5aKP9wpKtvnNBZrVv1srI8tU6xvFMzG8crLNcMj/8Xl/WWmo/oAP/40p1g==" crossorigin="anonymous" />
</head>

<body class="container-fluid">
    <?php
    session_start();
    require("header.php");

    // Create connection with MySQL database
    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $booking = $connection->query("SELECT * FROM bookings WHERE id=" . $_GET['id'])->fetch_assoc();
    ?>

    <?php
    if (isset($_SESSION['bookings'])) {
        // Write bookings into bookings.xml
        $dom = new DomDocument();
        $bookingDetails = $dom->createElement('bookingDetails');
        $dom->appendChild($bookingDetails);

        $bookingElement = $dom->createElement('booking');
        $bookingDetails->appendChild($bookingElement);

        $accountCardNumber = $dom->createElement('accountCardNumber');
        $accountCardNumber->appendChild($dom->createTextNode($_SESSION['bookings']['accountCardNumber']));
        $bookingElement->appendChild($accountCardNumber);

        $month = $dom->createElement('month');
        $month->appendChild($dom->createTextNode($_SESSION['bookings']['month']));
        $bookingElement->appendChild($month);

        $year = $dom->createElement('year');
        $year->appendChild($dom->createTextNode($_SESSION['bookings']['year']));
        $bookingElement->appendChild($year);

        $CvvCvc = $dom->createElement('CvvCvc');
        $CvvCvc->appendChild($dom->createTextNode($_SESSION['bookings']['CvvCvc']));
        $bookingElement->appendChild($CvvCvc);

        $accountCardName = $dom->createElement('accountCardName');
        $accountCardName->appendChild($dom->createTextNode($_SESSION['bookings']['accountCardName']));
        $bookingElement->appendChild($accountCardName);

        $dom->save("bookings.xml");
    }


    unset($_SESSION['guests']);
    unset($_SESSION['departFlight']);
    unset($_SESSION['returnFlight']);
    ?>
    <h4 style="text-align: center;">Payment confirmed. Thank you for choosing AirAsia!</h4>
    <?php
    echo "<p style='text-align: center;'>Booking ID: " . $booking['id'] . "</p>";
    echo "<p style='text-align: center;'>Transaction ID: " . $booking['transaction_no'] . "</p>";
