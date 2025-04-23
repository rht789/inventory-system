<?php
session_start();
require_once 'utils.php';
require_once '../db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'login':
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    
        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_profile_picture'] = $user['profile_picture'] ?? 'default.png';

            echo json_encode(['success' => true]);
            
            
        } else {
            echo json_encode(['error' => 'Invalid credentials']);
        }
        break;
    
        case 'forgot':
            $email = $_POST['email'];
        
            // Check if user exists with this email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        
            if (!$user) {
                echo json_encode(['error' => 'No user found with this email.']);
                break;
            }
        
            // Generate reset token and expiry
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
            // Update token and expiry in DB
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
            $stmt->execute([$token, $expiry, $email]);
        
            // Construct the reset link
            $link = "http://localhost/inventory-system/resetpassword.php?token=$token";
            // $body = "Click <a href='$link'>here</a> to reset your password.";
            $body = "
                <div style=\"font-family: Arial, sans-serif; font-size: 14px; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 6px;\">
                <h2 style=\"color: #111; text-align: center;\">Reset Your Password</h2>

                <p>Hello,</p>

                <p>We received a request to reset your password for your <strong>Smart Inventory Management System</strong> account.</p>

                <p>Click the button below to set a new password:</p>

                <p style=\"text-align: center;\">
                    <a href=\"$link\" style=\"background-color: #000; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;\">Reset Password</a>
                </p>

                <p>If the button above doesn't work, you can also copy and paste the following URL into your browser:</p>
                <p style=\"word-break: break-all; background-color: #f9f9f9; padding: 10px; border-radius: 4px; font-size: 13px;\">$link</p>

                <p><strong>Note:</strong> This link is valid for a limited time only. If you didn’t request a password reset, you can safely ignore this email.</p>

                <hr style=\"margin: 20px 0; border: none; border-top: 1px solid #ddd;\">
                <p style=\"font-size: 12px; color: #888;\">Need help? Contact your system administrator.</p>
                <p style=\"font-size: 12px; color: #aaa; text-align: center;\">&copy; " . date('Y') . " SmartInventory. All rights reserved.</p>
                </div>
                ";

        
            // Send email
            if (sendMail($email, "Reset Your Password", $body)) {
                echo json_encode(['success' => 'Reset email sent.']);
            } else {
                echo json_encode(['error' => 'Failed to send email.']);
            }
            break;
        
        case 'reset':
            $token = $_POST['token'];
            $newPassword = $_POST['new_password'];
    
            $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
    
            if (!$user) {
                echo json_encode(['error' => 'Invalid or expired token.']);
                break;
            }
    
            if (strtotime($user['reset_token_expiry']) < time()) {
                echo json_encode(['error' => 'Token expired.']);
                break;
            }
    
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            $stmt->execute([$hashedPassword, $token]);
    
            echo json_encode(['success' => 'Password has been reset.']);
            break;
    

    default:
        echo json_encode(['error' => 'Invalid action']);
}