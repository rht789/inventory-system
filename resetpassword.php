<?php
session_start();

// Sanitize token input
$token = isset($_GET['token']) ? htmlspecialchars(trim($_GET['token']), ENT_QUOTES, 'UTF-8') : '';

// Simple CSRF token function if it doesn't exist
if (!function_exists('getCsrfField')) {
    function getCsrfField() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
}

// Validate token (basic validation)
if (!empty($token)) {
    // Basic validation - should be a hexadecimal string of at least 32 chars
    if (!ctype_xdigit($token) || strlen($token) < 32) {
        $token = '';
    }
}
?>
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
                <input type="hidden" name="token" value="<?= $token ?>">
                <div>
                    <label class="block mb-1">New Password</label>
                    <input type="password" name="new_password" class="w-full border p-2 rounded" required minlength="8">
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long</p>
                </div>
                <!-- CSRF Token -->
                <?php echo getCsrfField(); ?>
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
            
            // Client-side validation
            const password = this.elements['new_password'].value;
            if (password.length < 8) {
                const msg = document.getElementById("resetMsg");
                msg.classList.remove("hidden", "bg-red-500", "bg-green-600");
                msg.classList.add("bg-red-500");
                msg.textContent = "Password must be at least 8 characters long";
                return;
            }
            
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