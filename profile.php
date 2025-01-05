<?php
session_start();
include('db.php');

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user email and name from session
$email = $_SESSION['email'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';

// Initialize statistics variables
$total_games = 0;
$total_wins = 0;
$total_losses = 0;
$win_rate = 0;

// Calculate Total Games
$stmt = $conn->prepare(
    "SELECT COUNT(*) as total_games FROM scores 
     WHERE player1_name = ? OR player2_name = ?"
);
$stmt->bind_param("ss", $name, $name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_games = $row['total_games'];
}
$stmt->close();

// Calculate Total Wins
$stmt = $conn->prepare(
    "SELECT COUNT(*) as total_wins FROM scores 
     WHERE (player1_name = ? AND ping_score > pong_score) 
     OR (player2_name = ? AND pong_score > ping_score)"
);
$stmt->bind_param("ss", $name, $name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_wins = $row['total_wins'];
}
$stmt->close();

// Calculate Total Losses
$total_losses = $total_games - $total_wins;

// Calculate Win Rate
if ($total_games > 0) {
    $win_rate = round(($total_wins / $total_games) * 100, 2);
}

// Fetch leaderboard data based on win rate
$leaderboard_data = [];
$stmt = $conn->prepare(
    "SELECT name, 
            (SELECT COUNT(*) FROM scores WHERE (player1_name = users.name AND ping_score > pong_score) OR (player2_name = users.name AND pong_score > ping_score)) AS wins, 
            (SELECT COUNT(*) FROM scores WHERE player1_name = users.name OR player2_name = users.name) AS games,
            ROUND((SELECT COUNT(*) FROM scores WHERE (player1_name = users.name AND ping_score > pong_score) OR (player2_name = users.name AND pong_score > ping_score)) /
                  (SELECT COUNT(*) FROM scores WHERE player1_name = users.name OR player2_name = users.name) * 100, 2) AS win_rate
     FROM users
     ORDER BY win_rate DESC
     LIMIT 10"
);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $leaderboard_data[] = $row;
}
$stmt->close();

// Pagination for game history
$limit = 5; // Matches per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare(
    "SELECT player1_name, player2_name, ping_score, pong_score, game_date 
     FROM scores 
     WHERE player1_name = ? OR player2_name = ?
     ORDER BY game_date DESC
     LIMIT ? OFFSET ?"
);
$stmt->bind_param("ssii", $name, $name, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$game_history = [];
while ($row = $result->fetch_assoc()) {
    $game_history[] = $row;
}
$stmt->close();

// Total matches for pagination
$stmt = $conn->prepare(
    "SELECT COUNT(*) as total_matches FROM scores 
     WHERE player1_name = ? OR player2_name = ?"
);
$stmt->bind_param("ss", $name, $name);
$stmt->execute();
$result = $stmt->get_result();
$total_matches = 0;
if ($row = $result->fetch_assoc()) {
    $total_matches = $row['total_matches'];
}
$stmt->close();

$total_pages = ceil($total_matches / $limit);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update Name and Password
    if (isset($_POST['update_profile'])) {    // Get the new name (if any)
        $new_password = $_POST['password'] ?? '';  // Get the new password (if any)
        $confirm_password = $_POST['confirm_password'] ?? ''; // Get the confirm password

        // Validate form inputs
        if (!empty($new_password) && $new_password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            // Update name if provided
            if (!empty($new_name)) {
                // Prepare SQL query to update name
                $stmt = $conn->prepare("UPDATE users SET name = ? WHERE email = ?");
                $stmt->bind_param("ss", $new_name, $email);
                if ($stmt->execute()) {
                    $_SESSION['name'] = $new_name; // Update session with new name
                    $success_message = "Name updated successfully.";
                } else {
                    $error_message = "Failed to update name.";
                }
                $stmt->close();
            }

            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password
                // Prepare SQL query to update password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);
                if ($stmt->execute()) {
                    $success_message = "Password updated successfully.";
                } else {
                    $error_message = "Failed to update password.";
                }
                $stmt->close();
            }
        }
    }

    // Delete Account
    if (isset($_POST['delete_account'])) {
        // Prepare SQL query to delete the user from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // Destroy session and redirect to the home page (index.html)
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Failed to delete account.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
        }
        .card {
            background: #fff;
            color: #333;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
            padding: 20px;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .btn-custom {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1rem;
            transition: 0.3s;
            width: 100%;
        }
        .btn-custom:hover {
            background: linear-gradient(to right, #2575fc, #6a11cb);
        }
        .list-group-item {
            background: #f8f9fa;
            border: none;
        }
        .winner {
            color: green;
            font-weight: bold;
        }
        .loser {
            color: red;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            color: #6a11cb;
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #6a11cb;
            color: white;
        }
        .pagination a:hover {
            background-color: #2575fc;
            color: white;
        }
        #chartdiv {
            width: 100%;
            height: 300px;
        }
        .stats-chart-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .stats-section, .chart-section {
            width: 48%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Profile</h3>
            </div>
            <div class="card-body">
                <!-- Display error message -->
                <?php if (!empty($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
                <!-- Display success message -->
                <?php if (!empty($success_message)) { echo "<div class='alert alert-success'>$success_message</div>"; } ?>

                <!-- Display current profile details -->
                <h5>Email: <?php echo htmlspecialchars($email); ?></h5>
                <h5>Name: <?php echo htmlspecialchars($name ? $name : "Not set"); ?></h5>

                <!-- If user clicked "Edit Profile", show the update form -->
                <?php if (!isset($_POST['edit_profile'])): ?>
                    <form method="POST" action="" style="margin-top: 20px;">
                        <button type="submit" name="edit_profile" class="btn btn-primary btn-custom">Edit Profile</button>
                    </form>
                <?php endif; ?>

                <?php if (isset($_POST['edit_profile'])): ?>
                    <form method="POST" action="" class="form-container">
                        

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary btn-custom">Update Profile</button>
                    </form>

                    <!-- Delete Account Form -->
                    <form method="POST" action="" style="margin-top: 20px;">
                        <button type="submit" name="delete_account" class="btn btn-danger btn-custom">Delete Account</button>
                    </form>
                <?php endif; ?>

                <!-- Go Back Button -->
                <div class="mt-3">
                    <a href="main.php" class="btn btn-secondary btn-custom">Back to Menu</a>
                </div>
            </div>
        </div>

        
      <!-- Game Statistics and Chart Side-by-Side -->
      <div class="card">
            <div class="card-header">
                <h5>Your Game Statistics</h5>
            </div>
            <div class="card-body">
                <div class="stats-chart-wrapper">
                    <div class="stats-section">
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Total Games Played:</strong> <?php echo $total_games; ?></li>
                            <li class="list-group-item"><strong>Wins:</strong> <?php echo $total_wins; ?></li>
                            <li class="list-group-item"><strong>Losses:</strong> <?php echo $total_losses; ?></li>
                            <li class="list-group-item"><strong>Win Rate:</strong> <?php echo $win_rate; ?>%</li>
                        </ul>
                    </div>
                    <div class="chart-section">
                        <div id="chartdiv"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="card">
            <div class="card-header">
                <h5>Leaderboard</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($leaderboard_data as $entry): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($entry['name']); ?></strong><br>
                            Wins: <?php echo $entry['wins']; ?> | Games: <?php echo $entry['games']; ?> | Win Rate: <?php echo $entry['win_rate']; ?>%
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Game History -->
        <div class="card">
            <div class="card-header">
                <h5>Your Game History</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($game_history as $game): ?>
                        <li class="list-group-item">
                            <span class="winner">Player 1: <?php echo htmlspecialchars($game['player1_name']); ?> (<?php echo $game['ping_score']; ?>)</span><br>
                            <span class="loser">Player 2: <?php echo htmlspecialchars($game['player2_name']); ?> (<?php echo $game['pong_score']; ?>)</span><br>
                            <span class="text-muted">Game Date: <?php echo htmlspecialchars($game['game_date']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Pagination -->
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
           // amCharts: Create a Pie Chart
           am5.ready(function() {
            var root = am5.Root.new("chartdiv");
            root.setThemes([am5themes_Animated.new(root)]);
            var chart = root.container.children.push(
                am5percent.PieChart.new(root, {
                    layout: root.verticalLayout
                })
            );
            var series = chart.series.push(
                am5percent.PieSeries.new(root, {
                    valueField: "value",
                    categoryField: "category"
                })
            );
            series.data.setAll([
                { category: "Wins", value: <?php echo $total_wins; ?> },
                { category: "Losses", value: <?php echo $total_losses; ?> }
            ]);
            chart.children.push(
                am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    layout: root.horizontalLayout
                })
            );
            series.appear(1000, 100);
        });
    </script>
</body>
</html>
