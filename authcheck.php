<?php
// // Start session only if not already started
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Redirect to login page if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Require a specific role (e.g., 'admin')
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        echo "Access Denied.";
        exit;
    }
}

// Allow access if user's role is in the allowed list
function allowRoles($roles = []) {
    requireLogin();
    if (!in_array($_SESSION['user_role'], $roles)) {
        echo "Access Denied.";
        exit;
    }
}

// Access helpers
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUsername() {
    return $_SESSION['user_username'] ?? 'Guest';
}

function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getUserProfilePic() {
    return $_SESSION['user_profile_picture'] ?? 'default.png';
}
