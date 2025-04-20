<?php
session_start();
require 'db.php';

$error = '';
$success = '';

// Run signup logic on form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';
    $role = $_POST["role"] ?? 'user';

    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }

    if (empty($error)) {
        try {
            // Check if user already exists
            $check = $pdo->prepare("SELECT email FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $error = "Email already registered.";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert into DB
                $stmt = $pdo->prepare("INSERT INTO users (email, username, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $username, $hashedPassword, $role]);

                $success = "Registration successful! You can now <a href='login.php'>log in</a>.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartInventory - Sign Up</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .signup-container h2 {
            margin-bottom: 5px;
        }

        .signup-container p {
            margin-bottom: 20px;
            color: gray;
        }

        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #eef2ff;
        }

        .btn {
            background-color: #000;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            width: 100%;
            cursor: pointer;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .login-link {
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2><strong>SmartInventory</strong></h2>
    <p>Create your account</p>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Create a password" required>
        </div>

        <div class="input-group">
            <label for="role">Role</label>
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn">Sign Up</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
