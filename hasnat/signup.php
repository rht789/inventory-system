<?php
session_start();
require '../db.php'; // adjust path as needed

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff'; // optional: allow selection

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check for duplicate username or email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
        $stmt->execute(['email' => $email, 'username' => $username]);
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role)
                VALUES (:username, :email, :password_hash, :role)
            ");

            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $hashed_password,
                'role' => $role
            ]);

            $success = "User registered successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - SmartInventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-md rounded-xl w-full max-w-md p-8">

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Register User</h2>
        <p class="text-sm text-center text-gray-500 mb-6">Fill in the form to create an account</p>

        <!-- Message Display -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form method="POST" action="">
            <!-- Username -->
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-medium mb-1">Username</label>
                <input type="text" name="username" id="username" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label for="role" class="block text-gray-700 font-medium mb-1">Role (optional)</label>
                <select name="role" id="role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition duration-200">
                Register
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>

</body>
</html>

