<?php
session_start();
include "db.php"; // Database connection (Availability)

$error_message = "";
$success_message = "";

// Show success message from signup
if (isset($_SESSION['account_created'])) {
    $success_message = $_SESSION['account_created'];
    unset($_SESSION['account_created']);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // CONFIDENTIALITY: only allow access if username exists
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashedPassword);
        $stmt->fetch();

        // CONFIDENTIALITY: verify hashed password
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            header("Location: dashboard.php"); // Redirect after login
            exit();
        } else {
            $error_message = "Incorrect password!"; // Prevent unauthorized access
        }
    } else {
        $error_message = "No user found with that username!"; // Prevent unauthorized access
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Booking</title>
<link rel="stylesheet" href="login.css">
</head>
<body>
<div class="login-container">

    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <input type="text" name="username" required placeholder="Username">
        </div>
        <div class="input-group">
            <input type="password" name="password" required placeholder="Password">
        </div>
        <button type="submit" class="btn">Login</button>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </form>
</div>
</body>
</html>
