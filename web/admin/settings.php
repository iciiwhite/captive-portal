<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { header('Location: auth.php'); exit; }
$configFile = '/etc/captive-portal.ini';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newConfig = "[general]\n";
    $newConfig .= "portal_name = {$_POST['portal_name']}\n";
    $newConfig .= "portal_ip = {$_POST['portal_ip']}\n";
    $newConfig .= "default_redirect = {$_POST['default_redirect']}\n";
    $newConfig .= "session_timeout_minutes = {$_POST['session_timeout']}\n";
    $newConfig .= "quota_time_minutes = {$_POST['quota_time']}\n";
    $newConfig .= "quota_bandwidth_mb = {$_POST['quota_bandwidth']}\n";
    $newConfig .= "auth_method = {$_POST['auth_method']}\n\n[admin]\n";
    $newConfig .= "username = {$_POST['admin_user']}\n";
    $newConfig .= "password_hash = {$_POST['admin_hash']}\n";
    file_put_contents($configFile, $newConfig);
    $msg = "Settings updated";
}
$config = parse_ini_file($configFile, true);
?>
<!DOCTYPE html>
<html><head><title>Settings</title><link rel="stylesheet" href="../style.css"></head>
<body>
<div class="container"><div class="card">
    <h2>Portal Settings</h2>
    <?php if(isset($msg)) echo "<p>$msg</p>"; ?>
    <form method="post">
        <input name="portal_name" value="<?= $config['general']['portal_name'] ?>">
        <input name="portal_ip" value="<?= $config['general']['portal_ip'] ?>">
        <input name="default_redirect" value="<?= $config['general']['default_redirect'] ?>">
        <input name="session_timeout" value="<?= $config['general']['session_timeout_minutes'] ?>">
        <input name="quota_time" value="<?= $config['general']['quota_time_minutes'] ?>">
        <input name="quota_bandwidth" value="<?= $config['general']['quota_bandwidth_mb'] ?>">
        <select name="auth_method">
            <option <?= $config['general']['auth_method']=='social'?'selected':'' ?>>social</option>
            <option <?= $config['general']['auth_method']=='sms'?'selected':'' ?>>sms</option>
            <option <?= $config['general']['auth_method']=='click'?'selected':'' ?>>click</option>
            <option <?= $config['general']['auth_method']=='voucher'?'selected':'' ?>>voucher</option>
        </select>
        <input name="admin_user" value="<?= $config['admin']['username'] ?>">
        <input name="admin_hash" placeholder="New password hash (BCRYPT)" value="<?= $config['admin']['password_hash'] ?>">
        <button type="submit">Save</button>
    </form>
    <a href="index.php">Back</a>
</div></div>
</body>
</html>