<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "game_pong";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get data from POST request
$player1_name = $_POST['player1_name'] ?? 'Player 1';
$player2_name = $_POST['player2_name'] ?? 'Player 2';
$ping_score = $_POST['ping_score'] ?? 0;
$pong_score = $_POST['pong_score'] ?? 0;

// Insert or update the scores table
$sql = "INSERT INTO scores (player1_name, player2_name, ping_score, pong_score, game_date)
        VALUES (?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $player1_name, $player2_name, $ping_score, $pong_score);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Game scores saved successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save game scores: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
