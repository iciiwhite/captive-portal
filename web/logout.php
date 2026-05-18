<?php
session_start();
$config = parse_ini_file('/etc/captive-portal.ini', true);
$db = new SQLite3($config['database']['db_path']);
$token = $_COOKIE['session_token'] ?? '';
if ($token) {
    $stmt = $db->prepare('DELETE FROM sessions WHERE token = :token');
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->execute();
    setcookie('session_token', '', time() - 3600, '/');
}
header('Location: index.html');
exit;
?>