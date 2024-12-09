<?php
session_start();

// Database connection settings
$servername = "localhost";  // Database host
$username = "root";         // Database username
$password = "";             // Database password (use your own)
$dbname = "login_system";   // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = ''; // Store error message for login failure

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Validate input
    if (empty($user) || empty($pass)) {
        $error_message = "Please fill all details.";
    } else {
        // Prepare SQL query to fetch user from database
        $stmt = $conn->prepare("SELECT id, username, password, name FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $db_username, $db_password, $name);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            // Verify password using password_hash() comparison
            if (password_verify($pass, $db_password)) {
                // Set session and redirect to main menu
                $_SESSION['username'] = $name;
                header("Location: game.php");
                exit();
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "Username not found. Redirecting to Register Page...";
            // Redirect to register page after a short delay
            header("refresh:3;url=register.php");
            exit();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Same styles as your original HTML */
        h1 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        #FORMS {
            padding: 20px;
            border: 1px solid black;
            border-radius: 20px;
            background-color: white;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-custom {
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        .btn-link {
            text-align: center;
        }
        body {
            background-image: url("https://www.mindinventory.com/blog/wp-content/uploads/2021/06/expense-tracking-app.webp");
            background-size: cover;
            font-family: 'Arial', sans-serif;
        }
        .btn-container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>Log In</h1>
    <div id="FORMS" class="mx-auto col-md-6">
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button class="btn btn-primary btn-custom" type="submit">Login</button>
        </form>

        <div class="btn-container d-flex justify-content-center">
            <button class="btn btn-link" onclick="window.location.href='register.php'">Register</button>
        </div>

        <?php
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger mt-3'>$error_message</div>";
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
