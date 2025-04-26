<?php
session_start();
require_once '../db.php'; // <-- your database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Get current user ID from session
$user_id = $_SESSION['user_id'];

// Get form input
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(["error" => "New password and confirmation do not match"]);
    exit;
}

// Fetch current password hash from database
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
    exit;
}

// Verify old password
if (!password_verify($old_password, $user['password_hash'])) {
    http_response_code(400);
    echo json_encode(["error" => "Old password is incorrect"]);
    exit;
}

// Hash new password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update the password in database
$update_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$update_stmt->execute([$new_password_hash, $user_id]);

echo json_encode(["success" => "Password updated successfully"]);
?>
