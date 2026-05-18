# Configuration

Edit `config/config.ini` to customize.

## Portal Settings

```ini
[general]
portal_name = My WiFi
portal_ip = 192.168.4.1
default_redirect = https://google.com
session_timeout_minutes = 60
quota_time_minutes = 120
quota_bandwidth_mb = 100
auth_method = voucher
```

Change `auth_method` to: `social`, `sms`, `click`, `voucher`.

## Social Login Mock

Replace keys with real OAuth credentials in config.ini:

```ini
[social]
facebook_app_id = your_app_id
facebook_secret = your_secret
google_client_id = your_client_id
google_client_secret = your_secret
```

Mock accepts any login.

## SMS OTP Mock

Always accepts 123456 as OTP code.

```ini
[sms]
provider_mock = true
```

## Voucher Codes

Predefine vouchers in SQLite:

```bash
sqlite3 web/data/users.db "INSERT INTO vouchers (code, used) VALUES ('FREE123', 0);"
```

## Bandwidth Quotas

Monitor script enforces limits. Change in config.ini under `quota_bandwidth_mb`.

## Session Timeout

Set minutes under `session_timeout_minutes`.

## Admin Panel

Access http://192.168.4.1/admin. Password hash stored in config.ini. Change using:

```bash
php -r "echo password_hash('newpassword', PASSWORD_BCRYPT);"
```

Replace `admin_password_hash` in config.ini.