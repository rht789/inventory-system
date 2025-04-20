<?php
session_start();
require '../db.php';

$error = "";
$success = "";
$showForm = false;

// Handle form submission
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
                    $success = "Password updated successfully.</a>.";
                    // login page redirect
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}

// Handle token from URL and show form
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

<?php if ($error): ?>
    <div style="color: red; margin: 20px;"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green; margin: 20px;"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($showForm): ?>
    <form method="POST">
        <h3>Reset Your Password</h3>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required style="width: 100%; padding: 8px; margin-top: 5px;"><br><br>
        <button type="submit" style="padding: 10px 15px;">Update Password</button>
    </form>
<?php endif; ?>
