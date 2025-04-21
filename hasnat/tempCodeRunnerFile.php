<?php
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