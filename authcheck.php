<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only define functions if they haven't been defined already
if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole($role) {
        requireLogin();
        if ($_SESSION['user_role'] !== $role) {
            echo "Access Denied.";
            
            exit;
        }
    }
}

if (!function_exists('allowRoles')) {
    function allowRoles($roles = []) {
        requireLogin();
        if (!in_array($_SESSION['user_role'], $roles)) {
            echo "Access Denied.";
            exit;
        }
    }
}

if (!function_exists('getUserId')) {
    function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('getUsername')) {
    function getUsername() {
        return $_SESSION['user_username'] ?? 'Guest';
    }
}

if (!function_exists('getUserEmail')) {
    function getUserEmail() {
        return $_SESSION['user_email'] ?? null;
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}

if (!function_exists('getUserProfilePic')) {
    function getUserProfilePic() {
        return $_SESSION['user_profile_picture'] ?? 'default.png';
    }
}
