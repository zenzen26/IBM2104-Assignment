<?php
session_start();

// Unset the session 
unset($_SESSION);
// Destroy the session
session_destroy();

// Redirect to login page after logout
header('Location: index.php');
exit();

?>