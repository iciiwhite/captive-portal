<?php
session_start();
$config = parse_ini_file('/etc/captive-portal.ini', true);
$db = new SQLite3($config['database']['db_path']);

function getUserByMac($mac, $db) {
    $stmt = $db->prepare('SELECT * FROM sessions WHERE mac = :mac AND expires > datetime("now")');
    $stmt->bindValue(':mac', $mac, SQLITE3_TEXT);
    $res = $stmt->execute();
    return $res->fetchArray(SQLITE3_ASSOC);
}

function createSession($mac, $ip, $db, $config) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+' . $config['general']['session_timeout_minutes'] . ' minutes'));
    $stmt = $db->prepare('INSERT INTO sessions (mac, ip, token, expires, login_time) VALUES (:mac, :ip, :token, :expires, datetime("now"))');
    $stmt->bindValue(':mac', $mac, SQLITE3_TEXT);
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
    $stmt->execute();
    setcookie('session_token', $token, time() + 86400, '/');
    return $token;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getAuthMethod') {
    header('Content-Type: application/json');
    echo json_encode(['method' => $config['general']['auth_method']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $mac = $_SERVER['HTTP_X_FORWARDED_MAC'] ?? $_SERVER['REMOTE_ADDR'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $success = false;
    $error = '';

    if ($action === 'smsLogin') {
        $phone = $_POST['phone'] ?? '';
        $otp = $_POST['otp'] ?? '';
        if ($otp === '123456') {
            $success = true;
        } else {
            $error = 'Invalid OTP';
        }
    } elseif ($action === 'clickThrough') {
        $success = true;
    } elseif ($action === 'voucherLogin') {
        $voucher = $_POST['voucher'] ?? '';
        $stmt = $db->prepare('SELECT * FROM vouchers WHERE code = :code AND used = 0');
        $stmt->bindValue(':code', $voucher, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res->fetchArray();
        if ($row) {
            $upd = $db->prepare('UPDATE vouchers SET used = 1 WHERE code = :code');
            $upd->bindValue(':code', $voucher, SQLITE3_TEXT);
            $upd->execute();
            $success = true;
        } else {
            $error = 'Invalid voucher';
        }
    } elseif ($action === 'socialLogin') {
        $provider = $_GET['provider'] ?? '';
        $success = true;
    }

    if ($success) {
        $token = createSession($mac, $ip, $db, $config);
        $log = fopen('../logs/portal.log', 'a');
        fwrite($log, date('Y-m-d H:i:s') . " - Login $mac $ip\n");
        fclose($log);
        echo json_encode(['success' => true, 'redirect' => $config['general']['default_redirect']]);
    } else {
        echo json_encode(['success' => false, 'error' => $error]);
    }
    exit;
}
?>