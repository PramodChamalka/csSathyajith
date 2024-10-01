<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Simple Authentication (For demonstration purposes)
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username == 'admin' && $password == 'password') {
        header('Location: dashboard.php');
    } else {
        echo "<p>Invalid credentials</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="post">
            <h2>Admin Login</h2>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
