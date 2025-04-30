<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-2 text-center">Forgot Password</h2>
        <p class="text-sm text-center text-gray-600 mb-4">Enter your email to receive a password reset link</p>

        <form id="forgotForm" class="space-y-4">
            <div id="forgotMsg" class="hidden p-3 rounded text-sm text-white"></div>
            <div>
                <label class="block mb-1">Email Address</label>
                <input type="email" name="email" class="w-full border p-2 rounded" required>
            </div>
            <button type="submit" class="w-full bg-black text-white py-2 rounded">Send Reset Link</button>
        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="text-blue-600 text-sm">← Go back to login</a>
        </div>
    </div>

    <script>
    document.getElementById("forgotForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const res = await fetch('api/auth.php?action=forgot', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        const msg = document.getElementById("forgotMsg");
        msg.classList.remove("hidden", "bg-red-500", "bg-green-600");
        msg.classList.add(result.success ? "bg-green-600" : "bg-red-500");
        msg.textContent = result.success || result.error;
    });
    </script>


</body>
</html>