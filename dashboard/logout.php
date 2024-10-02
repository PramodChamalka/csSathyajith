<?php
session_start();

// Destroy the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session itself

// Redirect to the login page or any other page
header('Location: login.php');
exit();
?>
