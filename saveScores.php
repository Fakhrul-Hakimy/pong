<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pong_game";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
   }

    // Check if player data and score data is received
    if (isset($_POST['player1Name']) && isset($_POST['player2Name']) && isset($_POST['pingScore']) && isset($_POST['pongScore'])) {
        $player1Name = $_POST['player1Name'];
        $player2Name = $_POST['player2Name'];
        $pingScore = $_POST['pingScore'];
        $pongScore = $_POST['pongScore'];

        // Insert player names and scores into the database
        $sql = "INSERT INTO scores (player1_name, player2_name, ping_score, pong_score) VALUES ('$player1Name', '$player2Name', $pingScore, $pongScore)";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>