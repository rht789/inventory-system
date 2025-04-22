<?php 

session_start();
require 'db.php'; // adjust the path as needed

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


if ($_SESSION['role'] !== 'admin') {
    echo "<p style='color:red;'>Access denied: Admins only</p>";
    exit;
}   

// Logged-in user info
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($username); ?> (<?php echo $role; ?>)</h2>


<form method="post" style="margin-top: 20px;">
    <button type="submit" name="logout">Logout</button>
</form>

<?php

if (isset($_POST['logout'])) {
    session_unset();      // clear session variables
    session_destroy();    // destroy session
    header("Location: login.php");
    exit;
}
?>

</body>
</html>