<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { header('Location: auth.php'); exit; }
$config = parse_ini_file('/etc/captive-portal.ini', true);
$db = new SQLite3($config['database']['db_path']);
if (isset($_GET['disconnect'])) {
    $id = (int)$_GET['disconnect'];
    $db->exec("DELETE FROM sessions WHERE id = $id");
    header('Location: index.php');
    exit;
}
if (isset($_POST['generate_voucher'])) {
    $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $stmt = $db->prepare('INSERT INTO vouchers (code, used) VALUES (:code, 0)');
    $stmt->bindValue(':code', $code, SQLITE3_TEXT);
    $stmt->execute();
    $msg = "Voucher $code generated";
}
$vouchers = $db->query('SELECT * FROM vouchers');
?>
<!DOCTYPE html>
<html><head><title>Manage Users</title><link rel="stylesheet" href="../style.css"></head>
<body>
<div class="container"><div class="card">
    <h2>Manage Vouchers</h2>
    <?php if(isset($msg)) echo "<p>$msg</p>"; ?>
    <form method="post"><button type="submit" name="generate_voucher">Generate Voucher</button></form>
    <table border="1"><tr><th>Code</th><th>Used</th></tr>
    <?php while($v = $vouchers->fetchArray()) { echo "<tr><td>{$v['code']}</td><td>".($v['used']?'Yes':'No')."</td></tr>"; } ?>
    </table>
    <a href="index.php">Back</a>
</div></div>
</body>
</html>