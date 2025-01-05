<?php
session_start();

include('db.php');

$error_message = ''; // Store error message for login failure

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; // Updated to 'email'
    $pass = $_POST['password'];

    // Validate input
    if (empty($email) || empty($pass)) {
        $error_message = "Please fill all details.";
    } else {
        // Prepare SQL query to fetch user from database by email
        $stmt = $conn->prepare("SELECT id, email, password, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $db_email, $db_password, $name);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            // Verify password using password_hash() comparison
            if (password_verify($pass, $db_password)) {
                // Set session with email and optional name, and redirect to main menu
                $_SESSION['email'] = $db_email; // Store email in session
                $_SESSION['name'] = $name; // Optional, as 'name' can be NULL
                header("Location: main.php");
                exit();
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "Email not found. Redirecting to Register Page...";
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
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
            color: #fff;
        }
        .card {
            background: #fff;
            color: #333;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
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
        }
        .btn-custom:hover {
            background: linear-gradient(to right, #2575fc, #6a11cb);
        }
        .form-control {
            border-radius: 25px;
        }
        .alert {
            margin-top: 15px;
            border-radius: 25px;
        }
        .text-center a {
            color: #6a11cb;
            font-weight: bold;
        }
        .text-center a:hover {
            text-decoration: none;
        }
        .welcome-header {
            text-align: center;
            margin-bottom: 15px;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
        }
        .welcome-subheader {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <h1 class="welcome-header">Welcome to Pong Game</h1>
    <p class="welcome-subheader">Let's have fun...</p>
    <div class="card">
        <div class="card-header">
            Log In
        </div>
        <div class="card-body">
            <!-- Display error message -->
            <?php if (!empty($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>

            <!-- Login form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button class="btn btn-custom btn-block" type="submit">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="register.php">Don't have an account? Register here</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
