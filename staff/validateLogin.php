<?php 
session_start();

if (!isset($_SESSION['userId'])) {
    // User not logged in. Redirect to login
    header('Location: index.php');
    exit();
}
