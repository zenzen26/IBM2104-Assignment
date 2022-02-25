<div class="d-flex justify-content-between">
    <img src="images/airasia-logo.svg" alt="AirAsia Logo" height="100px">
    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['customerId'])) {
        echo '<a class="align-self-center" href="logout.php"><input class="btn btn-outline-primary btn-lg text-right mr-5" type="button" value="Logout"></a>';
    }
    ?>
</div>
<hr class="w-100">