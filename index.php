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
        /* Custom styling */
        body {
            background-size: cover;
            font-family: 'Arial', sans-serif;
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
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="card">
            <div class="card-header text-center">
                <h3>Log In</h3>
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
                    <button class="btn btn-primary btn-custom" type="submit">Login</button>
                </form>

                <div class="text-center mt-3">
                    <a href="register.php" class="btn btn-link">Don't have an account? Register here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
