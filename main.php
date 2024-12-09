<?php
session_start();

// Check if the user is logged in by verifying the session
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email']; // Retrieve the email from the session
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest'; // Optionally use the name if available

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-size: cover;
            font-family: 'Arial', sans-serif;
            text-align: center;
            padding-top: 50px;
        }
        .card-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card-body {
            padding: 30px;
        }
        .btn-custom {
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="card-container">
        <div class="card">
            <div class="card-header">
                <h3>Welcome, <?php echo htmlspecialchars($email); ?></h3>
                <p>Welcome to your dashboard, <?php echo htmlspecialchars($name); ?>!</p>
            </div>
            <div class="card-body">
                <!-- Game Button -->
                <a href="game.html" class="btn btn-primary btn-custom">Go to Game</a>

                <!-- Profile Button -->
                <a href="profile.php" class="btn btn-secondary btn-custom">Go to Profile</a>

                <!-- Logout Button (Optional) -->
                <div class="mt-3">
                    <a href="logout.php" class="btn btn-danger btn-custom">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
