<?php
session_start();
require 'db.php'; // Your PDO connection

$error = '';
$loginSuccess = false;

// ✅ Only run login logic if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    // If no validation error and fields are not empty
    if (empty($error) && !empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Debugging: Check if user exists
            if (!$user) {
                $error = "No account found with this email.";
            } else {
                // Debugging: Verify password
                if (password_verify($password, $user["password_hash"])) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["role"] = $user["role"];
                    $loginSuccess = true;
                } else {
                    $error = "The password you entered is incorrect.";
                }
            }
        } catch (PDOException $e) {
            $error = "An error occurred while connecting to the database. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartInventory - Login</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 5px;
        }

        .login-container p {
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

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #eef2ff;
        }

        .forgot-password {
            font-size: 13px;
            color: #555;
            margin: 10px 0;
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
    </style>
</head>
<body>

<div class="login-container">
    <h2><strong>SmartInventory</strong></h2>
    <p>Enter your Credentials to sign in</p>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($loginSuccess)): ?>
        <div class="success">
            Login Successful ✅<br>
            <strong>Username:</strong> <?= htmlspecialchars($_SESSION["username"]) ?><br>
            <strong>Role:</strong> <?= htmlspecialchars($_SESSION["role"]) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Enter your password here" required>
        </div>

        <div class="forgot-password">forgot password</div>

        <button type="submit" class="btn">Sign In</button>
    </form>
</div>

</body>
</html>


<?php
echo password_hash("Admin123", PASSWORD_DEFAULT);
