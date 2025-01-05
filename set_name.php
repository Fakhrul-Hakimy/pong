<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    if ($name) {
        $_SESSION['name'] = $name;
        echo json_encode(['status' => 'success', 'message' => 'Name set successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid name']);
    }
    exit;
}
?>
