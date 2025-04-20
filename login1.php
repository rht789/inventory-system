<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartInventory Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-md rounded-xl w-full max-w-md p-8">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">SmartInventory</h2>
    <p class="text-sm text-center text-gray-500 mb-6">Enter your credentials to sign in</p>

    <!-- Form -->
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

      <!-- ✅ Updated link -->
      <div class="text-sm text-right mb-4">
        <a href="reset-password.php" class="text-blue-600 hover:underline">Forgot password?</a>
      </div>

      <button type="submit"
              class="w-full bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition duration-200">
        Login
      </button>
    </form>
  </div>

</body>
</html>

