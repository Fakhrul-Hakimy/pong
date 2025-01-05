<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Menu</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%; margin: 0; 
            display: flex; flex-direction: column; 
            align-items: center; justify-content: flex-start;
            background: linear-gradient(to right, #6a11cb, #2575fc); 
            font-family: 'Arial', sans-serif; color: white;
        }
        .card {
            background: #fff; color: #333;
            border-radius: 15px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%; max-width: 400px; margin-bottom: 20px;
            margin-top: 20px;
        }
        .card-header, #lobby {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff; font-size: 1.5rem; text-align: center;
            padding: 20px; border-radius: 15px;
        }
        .btn-custom {
            background: linear-gradient(to right, #6a11cb, #2575fc); color: #fff;
            border: none; border-radius: 25px; padding: 10px 20px; font-size: 1rem;
            transition: 0.3s; margin: 10px;
        }
        .btn-custom:hover {
            background: linear-gradient(to right, #2575fc, #6a11cb);
        }
        .form-container, .button-container {
            text-align: center; margin-top: 20px;
            display: flex; justify-content: center; flex-wrap: wrap;
        }
        input[type="text"] {
            border-radius: 25px; border: 1px solid #ccc;
            padding: 10px; width: calc(100% - 24px); margin: 10px;
        }
        #pongCanvas {
    display: block;
    margin: 0 auto;
    border: 2px solid white;
}

    </style>
</head>
<body>
    <?php
    session_start();

    // Check if the user is logged in by verifying the session
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    $email = $_SESSION['email']; // Retrieve the email from the session
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest'; // Optionally use the name if available
    ?>
    <div class="card">
        
        <div class="card-header">
            Welcome, <?php echo htmlspecialchars($name); ?>
        </div>
        <div class="form-container">
            <button onclick="location.href='game2.html'" class="btn btn-custom">Go to Game</button>
            <button onclick="location.href='profile.php'" class="btn btn-custom">Go to Profile</button>
            <button onclick="location.href='logout.php'" class="btn btn-custom">Logout</button>
        </div>
    </div>

    <div id="lobby">
        Welcome to the Lobby!
        <input type="text" id="roomId" placeholder="Enter Room ID">
        <div class="button-container">
        <button id="createRoom" class="btn btn-custom">Create Room</button>
        <button id="joinRoom" class="btn btn-custom">Join Room</button>
        </div>
    </div>
    <br>
    <canvas id="pongCanvas" width="800" height="600" style="background-color: black; display: block;"></canvas>

    <script>
        const canvas = document.getElementById('pongCanvas');
        const context = canvas.getContext('2d');
        let gameState = {};

        const socket = new WebSocket('ws://localhost:8080');

        socket.onopen = () => console.log("Connected to the server.");

        socket.onmessage = function (event) {
    const data = JSON.parse(event.data);
    console.log("Received data:", data);

    switch (data.type) {
        case "gameStateUpdate":
            console.log("Updating game state with:", data.gameState);
            gameState = data.gameState;
            draw();
            break;

        case "startGame":
            console.log("Starting game");
            gameState = data.gameState; // Ensure `gameState` is updated
            startGame();
            break;

        case "lobbyCreated":
            console.log("Lobby created with ID:", data.lobbyId);
            alert(`Lobby created successfully! ID: ${data.lobbyId}`);
            break;

        case "gameOver":
            console.log(`Game over! Winner: ${data.winner}`);
            alert(`Game Over! The winner is: ${data.winner}`);
            // Reset UI to show the lobby again
            document.getElementById("lobby").style.display = "block";
            document.getElementById("pongCanvas").style.display = "none";
            break;

        default:
            console.error("Unhandled message type:", data.type);
            break;
    }
};




function startGame() {
    console.log('Game state at start:', gameState); // Log to debug the state
    document.getElementById('lobby').style.display = 'none';
    document.getElementById('pongCanvas').style.display = 'block';
    draw();
}
function broadcastGameState(lobbyId) {
    const lobby = lobbies[lobbyId];
    if (lobby.players.length === 2) {  // Make sure both players are present
        const state = JSON.stringify({
            type: 'startGame',
            gameState: lobby.gameState
        });
        lobby.players.forEach(player => {
            console.log(`Sending gameState to player: ${state}`);
            player.send(state);
        });
    }
}


function draw() {
    if (!gameState.players || !gameState.ball) {
        console.error("Game state is incomplete:", gameState);
        return; // Exit if data is incomplete
    }

    // Clear the canvas
    context.clearRect(0, 0, canvas.width, canvas.height);

    // Draw paddles
    context.fillStyle = 'white';
    gameState.players.forEach((player, index) => {
        const paddleX = index === 0 ? 30 : canvas.width - 40; // Position paddles
        const paddleY = player.y || 0; // Default position to 0 if undefined
        context.fillRect(paddleX, paddleY, 10, 100); // Paddle width = 10, height = 100
    });

    // Draw ball
    context.beginPath();
    context.arc(gameState.ball.x, gameState.ball.y, 10, 0, Math.PI * 2); // Ball radius = 10
    context.fill();
}



        function showGame() {
            document.getElementById('lobby').style.display = 'none';
            document.getElementById('pongCanvas').style.display = 'block';
            draw();
        }
let roomId = null;
let playerIndex = null; // Define the player index globally

document.getElementById('createRoom').addEventListener('click', function () {
    roomId = document.getElementById('roomId').value.trim();
    if (roomId) {
        console.log(`Creating room with ID: ${roomId}`);
        playerIndex = 0; // Creator is Player 0
        socket.send(
            JSON.stringify({
                type: 'createLobby',
                roomId: roomId,
                name: '<?php echo htmlspecialchars($name); ?>',
            })
        );
    } else {
        alert('Please enter a room ID.');
    }
});

document.getElementById('joinRoom').addEventListener('click', function () {
    roomId = document.getElementById('roomId').value.trim();
    if (roomId) {
        console.log(`Joining room with ID: ${roomId}`);
        playerIndex = 1; // Joiner is Player 1
        socket.send(
            JSON.stringify({
                type: 'joinLobby',
                roomId: roomId,
                name: '<?php echo htmlspecialchars($name); ?>',
            })
        );
    } else {
        alert('Please enter a room ID.');
    }
});

document.addEventListener('keydown', (event) => {
    let direction = 0;

    // Prevent default scrolling behavior for arrow keys
    if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
        event.preventDefault();
    }

    if (event.key === 'ArrowUp') {
        direction = -10; // Move up
    } else if (event.key === 'ArrowDown') {
        direction = 10; // Move down
    }

    if (direction !== 0 && playerIndex !== null) {
        // Send the paddle movement to the server
        socket.send(
            JSON.stringify({
                type: 'movePaddle',
                lobbyId: roomId,
                playerIndex: playerIndex,
                y: direction,
            })
        );
    }
});




    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
