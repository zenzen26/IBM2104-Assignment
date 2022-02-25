<?php
session_start();
if (isset($_SESSION['customerId'])) {
    // If user already logged in redirect to homepage
    header('Location: index.php');
    exit();
} else {

    $usernameInvalid = false;
    $emailInvalid = false;
    $nameInvalid = false;
    $passwordInvalid = false;
    $confirmPasswordInvalid = false;

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $connection = new mysqli('127.0.0.1', 'admin', null, 'ibm2104_assignment');

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        if (trim($_POST['username']) == "") {
            $usernameInvalid = "This field is required.";
        } else {
            $result = $connection->query("SELECT * FROM customers WHERE BINARY username = '" . $_POST['username'] . "'");
            if ($result->num_rows > 0) {
                // Username already exist
                $usernameInvalid = "Username already exists";
            }
        }

        if (trim($_POST['email']) == "") {
            $emailInvalid = "This field is required.";
        } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $emailInvalid = "Invalid email address.";
        }

        if (trim($_POST['name']) == "") {
            $nameInvalid = true;
        }

        if ($_POST['password'] == "") {
            $passwordInvalid = true;
        }

        if (!$passwordInvalid && $_POST['confirmPassword'] !== $_POST['password']) {
            $confirmPasswordInvalid = true;
        }

        if (!$usernameInvalid && !$emailInvalid && !$nameInvalid && !$passwordInvalid && !$confirmPasswordInvalid) {
            if ($connection->query("INSERT INTO customers (cust_name, contact, email, address, username, password) VALUES ('" . $_POST['name'] . "', '" . $_POST['contact'] . "', '" . $_POST['email'] . "', '" . $_POST['address'] . "', '" . $_POST['username'] . "', '" . $_POST['password'] . "')") === true) {
                $_SESSION['customerId'] = $connection->insert_id;

                // User logged in. Redirect user to homepage
                header('Location: index.php');
                exit();
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
    <?php require('head.php') ?>
    <title>Welcome to AirAsia</title>
</head>

<body class="d-flex flex-column h-100" style="background-image: url('images/airasia-background.jpg'); background-repeat: no-repeat; background-size: cover;">
    <div class="d-flex justify-content-between">
        <img src="images/airasia-logo.svg" alt="AirAsia Logo" height="150px">
        <a class="align-self-center" href="login.php"><input class="btn btn-outline-primary btn-lg text-right align-self-center mr-5" type="button" value="Login"></a>
    </div>

    <div class="container-fluid m-4">
        <div class="card col-4">
            <div class="card-body">
                <?php echo isset($error) ? '<div class="alert alert-danger">An error has occured. Please try again.</div>' : "" ?>
                <form action="signup.php" method="POST">
                    <div class="form-row">
                        <div class="form-group col">
                            <label for="username" class="h5">Username</label>
                            <input type="text" class="form-control <?php if ($usernameInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST['username'] ?? "" ?>" id="username" placeholder="Username" name="username">
                            <?php
                            if ($usernameInvalid) {
                                echo "<div class='invalid-feedback'>$usernameInvalid</div>";
                            } else {
                                echo '<small class="form-text text-muted">Required</small>';
                            }
                            ?>
                        </div>
                        <div class="form-group col">
                            <label for="email" class="h5">Email Address</label>
                            <input type="email" class="form-control <?php if ($emailInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST['email'] ?? "" ?>" id="email" placeholder="john@test.com" name="email">
                            <?php
                            if ($emailInvalid) {
                                echo "<div class='invalid-feedback'>$emailInvalid</div>";
                            } else {
                                echo '<small class="form-text text-muted">Required</small>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label for="name" class="h5">Name</label>
                            <input type="text" class="form-control <?php if ($nameInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST['name'] ?? "" ?>" id="name" placeholder="Name" name="name">
                            <?php
                            if ($nameInvalid) {
                                echo "<div class='invalid-feedback'>This field is required.</div>";
                            } else {
                                echo '<small class="form-text text-muted">Required</small>';
                            }
                            ?>
                        </div>
                        <div class="form-group col">
                            <label for="contact" class="h5">Contact Number</label>
                            <input type="number" class="form-control" value="<?php echo $_POST['contact'] ?? "" ?>" id="contact" placeholder="0123456789" name="contact">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label for="password" class="h5">Password</label>
                            <input type="password" class="form-control <?php if ($passwordInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST['password'] ?? "" ?>" id="password" placeholder="Password" name="password">
                            <?php
                            if ($passwordInvalid) {
                                echo "<div class='invalid-feedback'>This field is required.</div>";
                            } else {
                                echo '<small class="form-text text-muted">Required</small>';
                            }
                            ?>
                        </div>
                        <div class="form-group col">
                            <label for="confirmPassword" class="h5">Confirm Password</label>
                            <input type="password" class="form-control <?php if ($confirmPasswordInvalid) echo "is-invalid"; ?>" value="<?php echo $_POST['confirmPassword'] ?? "" ?>" id="confirmPassword" placeholder="Confirm Password" name="confirmPassword">
                            <?php
                            if ($confirmPasswordInvalid) {
                                echo "<div class='invalid-feedback'>Password does not match.</div>";
                            } else {
                                echo '<small class="form-text text-muted">Required</small>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address" class="h5">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $_POST['address'] ?? "" ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Signup</button>
                </form>
            </div>
        </div>
    </div>


    <?php require('scripts.php') ?>
</body>

</html>