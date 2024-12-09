<?php
// Database connection settings
$servername = "localhost";  // Database host
$username = "root";         // Database username
$password = "";             // Database password (use your own)
$dbname = "game_pong";      // Database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
