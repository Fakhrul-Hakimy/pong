<?php
session_start();

include('db.php');

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email']; // Retrieve the user's email from session
$name = isset($_SESSION['name']) ? $_SESSION['name'] : ''; // Retrieve the user's name (if set)

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Handle form submission to update name and password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update Name and Password
    if (isset($_POST['update_profile'])) {
        $new_name = $_POST['name'] ?? '';    // Get the new name (if any)
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
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="card-container">
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

                <!-- Show Update Profile Form if clicked -->
                <form method="POST" action="" style="margin-top: 20px;">
                    <button type="submit" name="edit_profile" class="btn btn-primary btn-custom">Edit Profile</button>
                </form>

                <!-- If user clicked "Edit Profile", show the update form -->
                <?php if (isset($_POST['edit_profile'])): ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">New Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter your name">
                        </div>

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
                    <a href="main.php" class="btn btn-secondary btn-custom">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
