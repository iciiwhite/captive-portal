<?php
session_start();
$config = parse_ini_file('/etc/captive-portal.ini', true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $storedHash = $config['admin']['password_hash'];
    if ($user === $config['admin']['username'] && password_verify($pass, $storedHash)) {
        $_SESSION['admin_logged'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Login</title><link rel="stylesheet" href="../style.css"></head>
<body>
<div class="container"><div class="card">
    <h2>Admin Login</h2>
    <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div></div>
</body>
</html>