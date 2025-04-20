<?php
session_start();
require '../db.php';
require '../hasnat/mailsender.php'; // Path to your sendMail() function

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Email is required.";
    } else {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "No user found with that email.";
        } else {
            
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token and expiry to DB
            $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email");
            $stmt->execute([
                'token' => $token,
                'expiry' => $expiry,
                'email' => $email
            ]);

            // Prepare reset link
            $resetLink = "http://localhost/inventory-system/hasnat/resetpassword.php?token=$token";

            // Send the email
            $subject = "Password Reset Request";
            $body = "<h3>Hello " . htmlspecialchars($user['username']) . ",</h3>
                    <p>You requested to reset your password.</p>
                    <p><a href='$resetLink'>Click here to reset your password</a></p>
                    <small>This link will expire in 1 hour.</small>";

                    // sendMail from mailsender.php
            $result = sendMail($email, $subject, $body, $user['username']);

            if ($result === true) {
                $success = "Password reset link sent to your email.";
            } else {
                $error = "Failed to send email. $result";
            }
        }
    }
}
?>


<?php if ($error): ?>
    <div style="color: red;"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green;"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST">
    <label>Email Address:</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Send Reset Link</button>
</form>
<a href="login.php" style="color: #007BFF; text-decoration: none;">
        go back to login?
    </a>

