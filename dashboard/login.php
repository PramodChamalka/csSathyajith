
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
        <form action="" method="post">
            <h2>Admin Login</h2>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <p>Have no account? <a href="signup.php">Signup</a></p>
    </div>
</body>
</html>

<?php
include_once('db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user with the provided username exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];  
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($_SESSION['role'] === 'admin') {
                header('Location: dashboard.php'); // Admin Dashboard
                exit();
            } else {
                // Redirect non-admin users to a different page
                echo "<script type='text/javascript'>
                        alert('You are not an admin, redirecting to user dashboard.');
                        window.location.href = '../index.html';
                      </script>";
                exit();
            }
        } else {
            echo "<p>Invalid credentials</p>";
        }
    } else {
        echo "<p>Invalid credentials</p>";
    }
}
?>
