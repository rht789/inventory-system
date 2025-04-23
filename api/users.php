<?php
include '../db.php';
include 'utils.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$override = $_POST['_method'] ?? null;

if ($method === 'POST' && $override === 'DELETE') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'No user ID provided.']);
        exit;
    }

    try {
        //cadmin should not delete himslef

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
        $stmt = $pdo->query("SELECT id, username, email, phone, role FROM users ORDER BY id ASC");
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
        // $password = $_POST['password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        // if (!$username || !$email || !$password) {
        if (!$username || !$email) {
            echo json_encode(['error' => 'Please fill all required fields.']);
            exit;
        }

        $password = bin2hex(random_bytes(4)); 
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $phone, $role]);
            // echo json_encode(['success' => 'User created successfully.']);


        // ✅ Send password via email
        $subject = "Welcome to SmartInventory!";
        // $body = "
        //     Hello <strong>$username</strong>,<br><br>
        //     Your account has been created as <strong>$role</strong>.<br>
        //     Use the following password to login: <strong>$password</strong><br><br>
        //     You can log in at: <a href='http://localhost/inventory-system/login.php'>Login</a><br><br>
        //     Please change your password after logging in.";
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
        
        if (sendMail($email, $subject, $body)) {
            echo json_encode(['success' => 'User created and password emailed.']);
        } else {
            echo json_encode(['error' => 'User created but failed to send email.']);
        }


        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                echo json_encode(['error' => 'Username or email already exists.']);
            } else {
                echo json_encode(['error' => 'Something went wrong.']);
            }
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method.']);
}