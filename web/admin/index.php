<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: auth.php');
    exit;
}
$config = parse_ini_file('/etc/captive-portal.ini', true);
$db = new SQLite3($config['database']['db_path']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Admin Dashboard</h2>
        <nav><a href="users.php">Users & Sessions</a> | <a href="settings.php">Settings</a> | <a href="../logout.php">Logout</a></nav>
        <h3>Active Sessions</h3>
        <table border="1" cellpadding="5">
            <tr><th>MAC</th><th>IP</th><th>Login Time</th><th>Expires</th><th>Action</th></tr>
            <?php
            $res = $db->query('SELECT * FROM sessions WHERE expires > datetime("now")');
            while($row = $res->fetchArray()) {
                echo "<tr><td>{$row['mac']}</td><td>{$row['ip']}</td><td>{$row['login_time']}</td><td>{$row['expires']}</td>";
                echo "<td><a href='users.php?disconnect={$row['id']}'>Disconnect</a></td></tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>