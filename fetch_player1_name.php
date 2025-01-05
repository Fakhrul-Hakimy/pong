<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['name'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

// Return Player 1's name
echo json_encode(["player1_name" => $_SESSION['name']]);
?>
