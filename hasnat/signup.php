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
<html>
<head>
    <title>Signup</title>
</head>
<body>
<h2>Register User</h2>

<!-- Message Display -->
<?php if ($error): ?>
    <div style="color: red;"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green;"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Signup Form -->
<form method="POST" action="">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role (optional):</label><br>
    <select name="role">
        <option value="staff">Staff</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Register</button>
</form>
</body>
</html>
