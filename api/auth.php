<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

require_once 'utils.php';
require_once '../db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'login':
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            echo json_encode(['error' => 'Email and password are required']);
            exit;
        }
    
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    
        if ($user && password_verify($password, $user['password_hash'])) {
            // Check user status before allowing login
            if ($user['status'] === 'active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_profile_picture'] = $user['profile_picture'] ?? 'default.png';
                
                // Regenerate session ID when logging in (security best practice)
                session_regenerate_id(true);

                // Update last login timestamp
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                echo json_encode(['success' => true]);
            } elseif ($user['status'] === 'inactive') {
                echo json_encode(['error' => 'Your account is currently inactive. Please contact the system administrator for assistance.']);
            } elseif ($user['status'] === 'suspended') {
                echo json_encode(['error' => 'Your account has been suspended. Please contact the system administrator for more information.']);
            } else {
                echo json_encode(['error' => 'Invalid account status. Please contact the system administrator.']);
            }
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
            $body = "Click <a href='$link'>here</a> to reset your password.";
        
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