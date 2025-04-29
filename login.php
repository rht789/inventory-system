<?php
session_start();

// ✅ Redirect logged-in users
if (isset($_SESSION['user_id'])) {
    header("Location: home.php"); // Changed from products.php to home.php
    exit;
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-2 text-center">Login</h2>

        <form id="loginForm" class="space-y-4">
            <div id="loginMsg" class="hidden p-3 rounded text-sm text-white"></div>
            <div>
                <label class="block mb-1">Email</label>
                <input type="email" name="email" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>
            <button type="submit" class="w-full bg-black text-white py-2 rounded">Login</button>
        </form>

        <div class="text-center mt-4">
            <a href="forgotpassword.php" class="text-blue-600 text-sm">Forgot your password?</a>
        </div>
    </div>

    <script>
    document.getElementById("loginForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const res = await fetch('api/auth.php?action=login', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        const msg = document.getElementById("loginMsg");
        msg.classList.remove("hidden", "bg-red-500", "bg-green-600");
        msg.classList.add(result.success ? "bg-green-600" : "bg-red-500");
        msg.textContent = result.success ? "Login successful!" : result.error;
        if (result.success) setTimeout(() => window.location.href = 'home.php', 1500); // Changed from products.php to home.php
    });
    </script>

<?php include 'footer.php'; ?>
</body>
</html>