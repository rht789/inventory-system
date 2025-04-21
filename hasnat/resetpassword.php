<?php
session_start();
require '../db.php'; // adjust path if needed

$error = "";
$success = "";
$showForm = false;

// POST: update password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');

    if (empty($newPassword)) {
        $error = "Password cannot be empty.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Invalid or expired token.";
        } else {
            $expiry = strtotime($user['reset_token_expiry']);
            if (time() > $expiry) {
                $error = "This password reset link has expired.";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
                $update->execute([
                    'password' => $hashedPassword,
                    'id' => $user['id']
                ]);

                if ($update->rowCount()) {
                    $success = "✅ Password updated successfully.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}

// GET: show form if token valid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = trim($_GET['token']);

    $stmt = $pdo->prepare("SELECT reset_token_expiry FROM users WHERE reset_token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Invalid or expired token.";
    } else {
        $expiry = strtotime($user['reset_token_expiry']);
        if (time() > $expiry) {
            $error = "This password reset link has expired.";
        } else {
            $showForm = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - SmartInventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md">
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
        <br><br>
        <a href="login.php" class="text-blue-600 hover:underline">Go back to login</a>
      </div>
    <?php endif; ?>

    <!-- Show form only if valid -->
    <?php if (!empty($showForm)): ?>
      <div class="bg-white shadow-md rounded-xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Reset Your Password</h2>

        <form method="POST" action="">
          <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">

          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">New Password</label>
            <input type="password" name="new_password" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <button type="submit"
                  class="w-full bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition duration-200">
            Update Password
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
