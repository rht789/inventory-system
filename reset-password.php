<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - SmartInventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-md rounded-xl w-full max-w-md p-8">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Reset Your Password</h2>
    <p class="text-sm text-center text-gray-500 mb-6">Enter a new password and confirm it below.</p>

    <form method="POST" action="">
      <!-- Hidden token field -->
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

      <!-- New Password -->
      <div class="mb-4">
        <label for="new_password" class="block text-gray-700 font-medium mb-1">New Password</label>
        <input type="password" name="new_password" id="new_password" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Confirm Password -->
      <div class="mb-6">
        <label for="confirm_password" class="block text-gray-700 font-medium mb-1">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Submit Button -->
      <button type="submit"
              class="w-full bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition duration-200">
        Update Password
      </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-6">
      Back to <a href="login.php" class="text-blue-600 hover:underline">Login</a>
    </p>
  </div>

</body>
</html>
