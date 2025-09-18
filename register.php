<?php
session_start();
include("db.php");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename']; // NULL allowed if user has no middle name
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // CONFIDENTIALITY & INTEGRITY: validate password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }

    // INTEGRITY: prevent duplicate email registration
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "This email is already registered!";
    }
    $stmt->close();

    // INTEGRITY: prevent duplicate username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "This username is already taken!";
    }
    $stmt->close();

    // If no errors, insert new account
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // CONFIDENTIALITY: store hashed password

        $stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, email, contact, address, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstname, $middlename, $lastname, $email, $contact, $address, $username, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['account_created'] = "Account created successfully!";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "There was an issue with your signup. Please try again!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account - Booking</title>
<link rel="stylesheet" href="register.css">
</head>
<body>
<div class="signup-container">
    <h2>Create an Account</h2>

    <!-- Show errors -->
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Signup Form -->
    <form action="register.php" method="POST">
        <div class="input-group">
            <input type="text" name="firstname" required placeholder="First Name">
        </div>
        <div class="input-group">
            <input type="text" name="middlename" placeholder="Middle Name">
        </div>
        <div class="input-group">
            <input type="text" name="lastname" required placeholder="Last Name">
        </div>
        <div class="input-group">
            <input type="email" name="email" required placeholder="Email">
        </div>
        <div class="input-group">
            <input type="text" name="contact" required placeholder="Contact Number">
        </div>
        <div class="input-group">
            <input type="text" name="address" required placeholder="Address">
        </div>
        <div class="input-group">
            <input type="text" name="username" required placeholder="Username">
        </div>
        <div class="input-group">
            <input type="password" name="password" required placeholder="Password">
        </div>
        <div class="input-group">
            <input type="password" name="confirm_password" required placeholder="Confirm Password">
        </div>
        <button type="submit">Sign Up</button>
    </form>

    <div class="back-button-container">
        <a href="login.php" class="back-button">Back</a>
    </div>
</div>
</body>
</html>
