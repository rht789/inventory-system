<?php
include '../db.php';
include 'utils.php'; // Include the utils.php file which has the sendMail function
header('Content-Type: application/json');

// Ensure PHP errors don't output to response
ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];
$override = $_POST['_method'] ?? null;

// Handle single user fetch by ID (for checking role before edit/delete)
if ($method === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, phone, role, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(['user' => $user]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle DELETE via POST with _method override
if ($method === 'POST' && $override === 'DELETE') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'No user ID provided.']);
        exit;
    }

    try {
        // Admin cannot delete himself
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount()) {
            echo json_encode(['success' => 'User deleted successfully.']);
        } else {
            echo json_encode(['error' => 'User not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to delete user.']);
    }
    exit;
}

switch ($method) {
    case 'GET':
        // Include profile_picture in the query
        $stmt = $pdo->query("SELECT id, username, email, phone, role, profile_picture FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $grouped = ['admin' => [], 'staff' => []];

        foreach ($users as $user) {
            $grouped[$user['role']][] = $user;
        }

        echo json_encode($grouped);
        break;

    case 'POST':
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        if (!$username || !$email) {
            echo json_encode(['error' => 'Please fill all required fields.']);
            exit;
        }

        try {
            // Generate password and hash
            $password = bin2hex(random_bytes(4)); 
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // First, create the user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $phone, $role]);
            
            // User created successfully
            $userId = $pdo->lastInsertId();
            
            // Prepare email body
            $body = "
            <div style=\"font-family: Arial, sans-serif; font-size: 14px; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 6px;\">
              <h2 style=\"color: #111; text-align: center;\">Welcome to SmartInventory</h2>
            
              <p>Hi <strong>$username</strong>,</p>
            
              <p>We're excited to let you know that your account has been successfully created for the <strong>Smart Inventory Management System</strong>.</p>
            
              <p><strong>Role:</strong> $role<br>
              <strong>Temporary Password:</strong> <span style=\"background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-family: monospace;\">$password</span></p>
            
              <p>You can log in using the button below:</p>
            
              <p style=\"text-align: center;\">
                <a href=\"http://localhost/inventory-system/login.php\" style=\"background-color: #000; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;\">Login to SmartInventory</a>
              </p>
            
              <p><strong>Important:</strong> For security reasons, please change your password immediately after logging in.</p>
            
              <hr style=\"margin: 20px 0; border: none; border-top: 1px solid #ddd;\">
              <p style=\"font-size: 12px; color: #888;\">If you did not request this account, please ignore this email or contact your administrator.</p>
            
              <p style=\"font-size: 12px; color: #aaa; text-align: center;\">&copy; " . date('Y') . " SmartInventory. All rights reserved.</p>
            </div>
            ";
            
            $subject = "Welcome to SmartInventory!";
            
            // Try to send email using the sendMail function from utils.php
            $emailSent = sendMail($email, $subject, $body);
            
            if ($emailSent) {
                echo json_encode(['success' => 'User created and password emailed.']);
            } else {
                // Return success but with a note about email failure
                echo json_encode([
                    'success' => 'User created successfully.', 
                    'note' => 'Email could not be sent. Password: ' . $password
                ]);
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                echo json_encode(['error' => 'Username or email already exists.']);
            } else {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method.']);
}