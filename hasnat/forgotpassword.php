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
            $resetLink = "http://localhost/Inventory_management_System/hasnat/resetpassword.php?token=$token";

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


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - SmartInventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-md rounded-xl w-full max-w-md p-8 mx-auto">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Forgot Password</h2>
    <p class="text-sm text-center text-gray-500 mb-6">Enter your email to receive a password reset link</p>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if (!empty($success)): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <!-- Reset Link Form -->
    <form method="POST" action="">
      <div class="mb-4">
        <label for="email" class="block text-gray-700 font-medium mb-1">Email Address</label>
        <input type="email" name="email" id="email" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <button type="submit"
              class="w-full bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition duration-200">
        Send Reset Link
      </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-6">
      <a href="login.php" class="text-blue-600 hover:underline">← Go back to login</a>
    </p>
  </div>

</body>
</html>

