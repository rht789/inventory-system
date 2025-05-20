<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only try to load autoloader if it exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
require '../vendor/autoload.php';
}

function sendMail($to, $subject, $body) {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // Fallback to basic mail function if PHPMailer isn't available
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Smart Inventory <smartinventorymailer@gmail.com>' . "\r\n";
        
        return mail($to, $subject, $body, $headers);
    }
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'smartinventorymailer@gmail.com';
        $mail->Password   = 'otpbwtjizhzpskpz';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('smartinventorymailer@gmail.com', 'Smart Inventory');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * CSRF Protection Functions
 */

/**
 * Generate a CSRF token and store it in the session
 * @return string The generated token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Create a hidden input field with the CSRF token
 * @return string HTML for the CSRF token input field
 */
function getCSRFTokenField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate a submitted CSRF token
 * @param string $token The token to validate
 * @return bool Whether the token is valid
 */
function validateCSRFToken($token) {
    if (!$token || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF token and die if invalid
 * @param string $token The token to verify
 */
function requireValidCSRFToken($token = null) {
    $token = $token ?? ($_POST['csrf_token'] ?? null);
    
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        die('CSRF token validation failed. Please try again.');
    }
}

/**
 * Session Security Functions
 */

/**
 * Configure session for enhanced security
 * - Sets secure and httpOnly flags (if HTTPS is available)
 * - Sets SameSite policy
 * - Sets session cookie path
 */
function secureSession() {
    try {
        $cookieParams = session_get_cookie_params();
        $path = $cookieParams['path'];
        // Only use secure flag if HTTPS is being used
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $httponly = true; // Prevent JavaScript access to session cookie
        $samesite = 'Lax'; // Prevents CSRF while allowing normal navigation
        
        // PHP 7.3.0+ supports SameSite flag
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => $cookieParams['lifetime'],
                'path' => $path,
                'domain' => $cookieParams['domain'],
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);
        } else {
            // For older PHP versions
            session_set_cookie_params(
                $cookieParams['lifetime'],
                $path . '; samesite=' . $samesite,
                $cookieParams['domain'],
                $secure,
                $httponly
            );
        }
        
        // Start the session if it's not started yet
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Force regenerate session ID periodically (every 30 minutes)
        if (!isset($_SESSION['last_regeneration']) || 
            (time() - $_SESSION['last_regeneration']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    } catch (Exception $e) {
        error_log('Session security error: ' . $e->getMessage());
        // Fallback to basic session start if the secure options fail
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}