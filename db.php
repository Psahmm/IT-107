<?php
// Database connection (Availability: ensures MySQL is accessible for users)
$host = "localhost";
$db   = "event_booking";  
$user = "root";           
$pass = "";               

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Availability: server must be running
}
?>
