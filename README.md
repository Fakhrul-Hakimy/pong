# Multiplayer Pong Game

This repository contains a multiplayer Pong game built using HTML, CSS, JavaScript, PHP, and Node.js. The game includes features such as a real-time WebSocket server, game statistics, a leaderboard, and a user profile system with authentication.

## Features

- Multiplayer real-time Pong game.
- Player profile management (edit profile, delete account).
- Leaderboard showcasing top players by win rate.
- Game statistics and history.
- Chart representation of game statistics using amCharts.

## Dependencies

### Server-Side Dependencies
- Node.js
- Express
- HTTP
- WebSocket
- Path

### Client-Side Dependencies
- amCharts
- Bootstrap 4

## Installation Guide

### Prerequisites
- Node.js installed on your system.
- A local server environment (e.g., XAMPP, WAMP) for running PHP and MySQL.

### Steps

1. Clone the repository:
```bash
git clone https://github.com/yourusername/multiplayer-pong-game.git
```

2. Navigate to the project directory:
```bash
cd multiplayer-pong-game
```

3. Install Node.js dependencies:
```bash
npm install express ws http path
```

4. Set up the database:
   - Import the `database.sql` file into your MySQL server.
   - Update the `db.php` file with your database credentials.

5. Start the Node.js server:
```bash
node server.js
```

6. Start the PHP server:
   - Place the project in your local server's `htdocs` folder (e.g., `xampp/htdocs`).
   - Access the project via `http://localhost/multiplayer-pong-game/`.

## File Structure

```
multiplayer-pong-game/
├── assets/
│   ├── css/
│   └── js/
├── server.js
├── index.php
├── profile.php
├── db.php
├── README.md
└── database.sql
```

## Usage

### Playing the Game
1. Open the application in your browser.
2. Log in or register as a new user.
3. Join or create a game room.
4. Play the Pong game in real time.

### Managing Profile
- Edit your profile details.
- Update your password.
- Delete your account.

### Leaderboard and Game Statistics
- View the leaderboard based on win rate.
- Analyze your game statistics with visual charts.
- Browse your game history with pagination.

## API Endpoints

### Node.js Server
- WebSocket endpoint: `ws://localhost:8080`
- Handles real-time game communication.

### PHP Backend
- User authentication and profile management.
- Fetching leaderboard and game history.
- Saving and retrieving game data.

## Dependencies
### Node.js Libraries
- `express`
- `ws`
- `http`
- `path`

### Front-End Libraries
- `Bootstrap 4`
- `amCharts`

## License
This project is licensed under the MIT License.


## Contributing

We welcome contributions! Please fork the repository, create a feature branch, and submit a pull request.

---

## License

This project is licensed under the MIT License. Feel free to modify and distribute it as needed.

---

## Contact

For any issues or suggestions, please contact us at [fakhrulhakimy93@gmail.com](mailto:fakhrulhakimy93@gmail.com).
```

