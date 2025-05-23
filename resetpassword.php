<?php $token = $_GET['token'] ?? ''; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-2 text-center">Reset Password</h2>

        <?php if (!$token): ?>
            <p class="text-center bg-red-100 text-red-600 p-3 rounded">Invalid or missing token.</p>
        <?php else: ?>
            <form id="resetForm" class="space-y-4">
                <div id="resetMsg" class="hidden p-3 rounded text-sm text-white"></div>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div>
                    <label class="block mb-1">New Password</label>
                    <input type="password" name="new_password" class="w-full border p-2 rounded" required>
                </div>
                <button type="submit" class="w-full bg-black text-white py-2 rounded">Reset</button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="login.php" class="text-blue-600 text-sm">← Go back to login</a>
        </div>
    </div>

    <script>
    const resetForm = document.getElementById("resetForm");
    if (resetForm) {
        resetForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const res = await fetch('api/auth.php?action=reset', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            const msg = document.getElementById("resetMsg");
            msg.classList.remove("hidden", "bg-red-500", "bg-green-600");
            msg.classList.add(result.success ? "bg-green-600" : "bg-red-500");
            msg.textContent = result.success || result.error;
            if (result.success) setTimeout(() => window.location.href = 'login.php', 2000);
        });
    }
    </script>

</body>
</html>