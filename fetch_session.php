<?php
session_start();
if (isset($_SESSION['email'])) {
    echo json_encode([
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name'] ?? 'Guest',
    ]);
} else {
    echo json_encode(['error' => 'User not logged in.']);
}
?>
