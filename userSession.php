<?php
// dashboard.php
session_start();

// Correct the isset() check and role validation
if (!isset($_SESSION['user_id'])) {
    // header('Location: login.php');
    echo "<script type='text/javascript'>
    alert('please login inorder to place order');
    window.location.href = 'dashboard/login.php';
  </script>";
    exit();
}

echo "Welcome, " . $_SESSION['username'] . "! Your role is: " . $_SESSION['role'];

// Add more dashboard content here
?>
