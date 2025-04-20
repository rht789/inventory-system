<?php
session_start();
require '../db.php'; 
$success = "";
$error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Both fields are required.";
        } else {

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // verify (plain vs hash)
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = "Invalid email or password.";
            } else {
                // session variables set
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $success = "Welcome, " . htmlspecialchars($user['username']) . "!";
                // Redirect 
                header("Location: dashboard.php"); exit;
            }
        }
    }

?>

<form method="POST" action="">
  <input type="email" name="email" required>
  <input type="password" name="password" required>
  <button type="submit">Login</button>
</form>
<a href="forgotpassword.php" style="color: #007BFF; text-decoration: none;">
        Forgot your password?
    </a>




<?php if ($error): ?>
    <div style="color: red;"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green;"><?php echo $success; ?></div>
<?php endif; ?>