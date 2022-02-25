<?php
session_start();
if (isset($_POST['username']) && isset($_POST['password'])) {
    $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $result = $connection->query("SELECT * FROM staff WHERE BINARY email = '$username' AND BINARY password = '$password';");

    if ($result->num_rows > 0) {
        $_SESSION['userId'] = $result->fetch_assoc()['id'];

        // User logged in. Redirect user to homepage
        header('Location: homepage.php');
        exit();
    } else {
        $error = true;
    }
} else if (isset($_SESSION['userId'])) {
    // If user already logged in redirect to homepage
    header('Location: homepage.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <?php require('../head.php') ?>
    <title>AirAsia Staff Portal</title>
</head>

<body class="d-flex flex-column h-100" style="background-image: url('../images/airasia-background.jpg'); background-repeat: no-repeat; background-size: cover;">
    <div><img class="justify-content-start" src="../images/airasia-logo.svg" alt="AirAsia Logo" height="150px"></div>

    <div class="d-flex mt-auto m-5 pb-5">
        <form action="index.php" method="POST">
            <?php echo isset($error) ? '<div class="alert alert-danger">Invalid username or password</div>' : "" ?>
            <div class="form-group">
                <label for="username" class="h5">Username</label>
                <input type="text" class="form-control form-control-lg" id="username" placeholder="Username" name="username">
            </div>
            <div class="form-group">
                <label for="password" class="h5">Password</label>
                <input type="password" class="form-control form-control-lg" id="password" placeholder="Password" name="password">
            </div>
            <button type="submit" class="btn btn-primary btn-lg">Login</button>
        </form>
    </div>

    <?php require('../scripts.php') ?>
</body>

</html>