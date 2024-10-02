<?php
// dashboard.php
session_start();

// Correct the isset() check and role validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // header('Location: login.php');
    echo "<script type='text/javascript'>
    alert('You are not an admin to login to the admin dashboard.');
    window.location.href = 'login.php';
  </script>";
    exit();
}

echo "Welcome, " . $_SESSION['username'] . "! Your role is: " . $_SESSION['role'];

// Add more dashboard content here
?>
