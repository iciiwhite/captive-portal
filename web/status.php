<?php
session_start();
$config = parse_ini_file('/etc/captive-portal.ini', true);
$db = new SQLite3($config['database']['db_path']);
$token = $_COOKIE['session_token'] ?? '';
$authenticated = false;
$used_mb = 0;
$used_min = 0;
$quota_mb = (int)$config['general']['quota_bandwidth_mb'];
$quota_min = (int)$config['general']['quota_time_minutes'];

if ($token) {
    $stmt = $db->prepare('SELECT * FROM sessions WHERE token = :token AND expires > datetime("now")');
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $res = $stmt->execute();
    $session = $res->fetchArray(SQLITE3_ASSOC);
    if ($session) {
        $authenticated = true;
        $login = strtotime($session['login_time']);
        $now = time();
        $used_min = round(($now - $login) / 60);
        $stmt2 = $db->prepare('SELECT SUM(bytes_up + bytes_down) as total FROM usage_log WHERE session_id = :sid');
        $stmt2->bindValue(':sid', $session['id'], SQLITE3_INTEGER);
        $res2 = $stmt2->execute();
        $row = $res2->fetchArray();
        $used_mb = round(($row['total'] ?? 0) / (1024*1024), 2);
    }
}
header('Content-Type: application/json');
echo json_encode([
    'authenticated' => $authenticated,
    'used_mb' => $used_mb,
    'quota_mb' => $quota_mb,
    'used_min' => $used_min,
    'quota_min' => $quota_min
]);
?>