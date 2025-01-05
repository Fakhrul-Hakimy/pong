const WebSocket = require("ws");
const http = require("http");
const express = require("express");
const path = require("path"); // Correctly included for use in path operations
const app = express();
const server = http.createServer(app);

// You had an extra parenthesis here which I have removed
app.use(express.static(".")); // Correctly serving static files from the current directory

// This will send the PHP file as plain text, not execute it
app.get("/", (req, res) => {
    res.sendFile(path.join(__dirname, "main.php")); // Use path.join for better path resolution
});

const mysql = require("mysql");

// MySQL database connection
const db = mysql.createConnection({
    host: "localhost",
    user: "root", // Replace with your MySQL username
    password: "", // Replace with your MySQL password
    database: "game_pong", // Replace with your database name
});

// Connect to the database
db.connect((err) => {
    if (err) {
        console.error("Database connection failed:", err);
        return;
    }
    console.log("Connected to the MySQL database.");
});

const wss = new WebSocket.Server({ server });
let lobbies = {};

function startGameLoop(lobbyId) {
    const lobby = lobbies[lobbyId];
    const fps = 60;
    const interval = 1000 / fps;

    // Broadcast initial game state
    broadcastGameState(lobbyId);

    lobby.intervalId = setInterval(() => {
        // Update ball position
        lobby.gameState.ball.x += lobby.gameState.ball.dx;
        lobby.gameState.ball.y += lobby.gameState.ball.dy;

        // Handle collisions and scoring
        handleCollisions(lobby);
        handleScoring(lobby);

        // Broadcast updated game state
        broadcastGameState(lobbyId);
    }, interval);
}



function updateGameState(lobby) {
    // Simplified example of updating game state
    lobby.gameState.ball.x += lobby.gameState.ball.dx;
    lobby.gameState.ball.y += lobby.gameState.ball.dy;

    // Handle collisions and scoring
    handleCollisions(lobby);
    handleScoring(lobby);
}

function handleCollisions(lobby) {
    // Ball collision with top and bottom boundaries
    if (lobby.gameState.ball.y <= 0 || lobby.gameState.ball.y >= 600) {
        lobby.gameState.ball.dy *= -1; // Reverse vertical direction
    }

    // Paddle collision detection
    const ball = lobby.gameState.ball;
    const players = lobby.gameState.players;

    // Check collision with Player 1 paddle
    if (
        ball.x <= 40 && // Ball near left paddle
        ball.y >= players[0].y &&
        ball.y <= players[0].y + 100 // Paddle height
    ) {
        ball.dx *= -1; // Reverse horizontal direction
        ball.x = 40; // Adjust ball position to prevent sticking
    }

    // Check collision with Player 2 paddle
    if (
        ball.x >= 760 && // Ball near right paddle
        ball.y >= players[1].y &&
        ball.y <= players[1].y + 100 // Paddle height
    ) {
        ball.dx *= -1; // Reverse horizontal direction
        ball.x = 760; // Adjust ball position to prevent sticking
    }
}


function handleScoring(lobby) {
    if (lobby.gameState.ball.x <= 0 || lobby.gameState.ball.x >= 800) {
        // Update scores
        if (lobby.gameState.ball.x <= 0) {
            lobby.gameState.players[1].score += 1; // Player 2 scores
        } else if (lobby.gameState.ball.x >= 800) {
            lobby.gameState.players[0].score += 1; // Player 1 scores
        }

        console.log("Scores:", lobby.gameState.players.map((p) => p.score));

        // Check if any player reached 5 points
        if (lobby.gameState.players[0].score === 5 || lobby.gameState.players[1].score === 5) {
            const winnerIndex =
                lobby.gameState.players[0].score === 5 ? 0 : 1;
            const loserIndex = winnerIndex === 0 ? 1 : 0;

            const winnerName = lobby.gameState.players[winnerIndex].name;
            const loserName = lobby.gameState.players[loserIndex].name;
            const winnerScore = lobby.gameState.players[winnerIndex].score;
            const loserScore = lobby.gameState.players[loserIndex].score;

            // Broadcast "gameOver" to both players
            const state = JSON.stringify({
                type: "gameOver",
                winner: winnerName,
            });

            lobby.players.forEach((player) => player.send(state));

            // Stop the game loop
            clearInterval(lobby.intervalId);

            // Insert game result into the database
            const query = `
                INSERT INTO scores (player1_name, player2_name, ping_score, pong_score, game_date)
                VALUES (?, ?, ?, ?, NOW())
            `;

            db.query(
                query,
                [winnerName, loserName, winnerScore, loserScore],
                (err, result) => {
                    if (err) {
                        console.error("Failed to insert game result:", err);
                    } else {
                        console.log("Game result saved to database.");
                    }
                }
            );

            console.log(`Game over! Winner: ${winnerName}`);
            return;
        }

        // Reset ball to the center
        resetBall(lobby);
    }
}



function resetBall(lobby) {
    // Reset ball to center after scoring
    lobby.gameState.ball.x = 400;
    lobby.gameState.ball.y = 300;
    lobby.gameState.ball.dx = 5 * (Math.random() > 0.5 ? 1 : -1);
    lobby.gameState.ball.dy = 5 * (Math.random() > 0.5 ? 1 : -1);
}

function broadcastGameState(lobbyId) {
    const lobby = lobbies[lobbyId];
    if (lobby) {
        const state = JSON.stringify({
            type: 'gameStateUpdate',
            gameState: lobby.gameState,
        });

        lobby.players.forEach(player => player.send(state));
    }
}



wss.on("connection", (ws) => {
    ws.on("message", (message) => {
        const data = JSON.parse(message);
        handleWebSocketMessage(ws, data);
    });
});

function handleWebSocketMessage(ws, data) {
    // Handling WebSocket messages based on type
    switch (data.type) {
        case 'createLobby':
            createLobby(ws, data);
            break;
        case 'joinLobby':
            joinLobby(ws, data);
            break;
        case 'movePaddle':
            movePaddle(data);
            break;
        default:
            console.error("Unknown message type received");
    }
}

function createLobby(ws, data) {
    const lobbyId = data.roomId;
    lobbies[lobbyId] = {
        players: [ws],
        gameState: {
            players: [
                { name: data.name || "Player 1", score: 0, y: 250 }, // Paddle in the middle
                { name: "Waiting for Player", score: 0, y: 250 }    // Paddle in the middle
            ],
            ball: { x: 400, y: 300, dx: 5, dy: 5 }, // Ball starts at center
        },
    };
    console.log(`Lobby ${lobbyId} created.`);
    ws.send(JSON.stringify({ type: "lobbyCreated", lobbyId }));
}


function joinLobby(ws, data) {
    const lobbyId = data.roomId;
    if (lobbies[lobbyId] && lobbies[lobbyId].players.length < 2) {
        lobbies[lobbyId].players.push(ws);
        lobbies[lobbyId].gameState.players[1] = { name: data.name || "Player 2", score: 0, y: 250 };
        startGameLoop(lobbyId);
        console.log(`Lobby ${lobbyId} is ready to start.`);
    } else {
        ws.send(JSON.stringify({ type: "error", message: "Lobby full or does not exist." }));
        console.log(`Failed to join lobby ${lobbyId}: Full or does not exist.`);
    }
}

function movePaddle(data) {
    const lobbyId = data.lobbyId;
    const playerIndex = data.playerIndex;
    const lobby = lobbies[lobbyId];

    if (lobby && lobby.gameState.players[playerIndex]) {
        // Update paddle position
        lobby.gameState.players[playerIndex].y += data.y;

        // Ensure paddle stays within canvas bounds
        lobby.gameState.players[playerIndex].y = Math.max(
            0,
            Math.min(500, lobby.gameState.players[playerIndex].y) // Canvas height (600) - paddle height (100)
        );

        // Broadcast updated game state
        broadcastGameState(lobbyId);
    }
}




    function startGame() {
        console.log('Game state at start:', gameState); // Log to debug the state
        document.getElementById('lobby').style.display = 'none';
        document.getElementById('pongCanvas').style.display = 'block';
        draw();
    }

    function handleCollisions(lobby) {
        // Ball collision with top and bottom boundaries
        if (lobby.gameState.ball.y <= 0 || lobby.gameState.ball.y >= 600) {
            lobby.gameState.ball.dy *= -1; // Reverse ball direction
        }
    
        // Paddle collision (adjust for paddle height)
        lobby.gameState.players.forEach(player => {
            if (lobby.gameState.ball.x < 40 && // Near player 1 paddle
                lobby.gameState.ball.y >= player.y &&
                lobby.gameState.ball.y <= player.y + 100) {
                lobby.gameState.ball.dx *= -1;
            }
    
            if (lobby.gameState.ball.x > 760 && // Near player 2 paddle
                lobby.gameState.ball.y >= player.y &&
                lobby.gameState.ball.y <= player.y + 100) {
                lobby.gameState.ball.dx *= -1;
            }
        });
    }
    

server.listen(8080, () => { console.log("Server started on port 8080"); });