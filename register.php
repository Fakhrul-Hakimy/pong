<?php
session_start();

include('db.php');

$error_message = ''; // Store error message for registration failure
$success_message = ''; // Store success message

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name']; // New field
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    // Validate input
    if (empty($name) || empty($email) || empty($pass) || empty($confirm_pass)) {
        $error_message = "Please fill all details.";
    } elseif ($pass !== $confirm_pass) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Email is already registered.";
        } else {
            // Hash the password before saving it to the database
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now log in.";
                // Optionally, redirect after a few seconds
                header("refresh:3;url=index.php");
                exit();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
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
        .eye-icon {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            Register
        </div>
        <div class="card-body">
            <!-- Display error or success message -->
            <?php if (!empty($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
            <?php if (!empty($success_message)) { echo "<div class='alert alert-success'>$success_message</div>"; } ?>

            <!-- Registration form -->
            <form method="POST" action="">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="input-group-append">
                <span class="input-group-text eye-icon" id="togglePassword1">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <div class="input-group">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            <div class="input-group-append">
                <span class="input-group-text eye-icon" id="togglePassword2">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>
    </div>
    <button class="btn btn-custom btn-block" type="submit">Register</button>
</form>


            <div class="text-center mt-3">
                <a href="index.php">Already have an account? Login here</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for the eye icon -->

    <script>
        // Toggle password visibility function
        $('#togglePassword1').click(function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        $('#togglePassword2').click(function() {
            const confirmPasswordField = $('#confirm_password');
            const type = confirmPasswordField.attr('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
    </script>
</body>
</html>
