<?php
session_start();
require '../db.php'; 

$success = "";
$error = "";

// Handle login form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid email or password.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartInventory Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-md rounded-xl w-full max-w-md p-8 mx-auto">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Login</h2>

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

    <!-- Login Form -->
    <form method="POST" action="">
      <div class="mb-4">
        <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" id="email" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="mb-4">
        <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" id="password" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Forgot Password -->
      <div class="text-right mb-4">
        <a href="forgotpassword.php" class="text-blue-600 text-sm hover:underline">
          Forgot your password?
        </a>
      </div>

      <!-- Submit Button -->
      <button type="submit"
              class="w-full bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition duration-200">
        Login
      </button>
    </form>
  </div>

</body>
</html>
