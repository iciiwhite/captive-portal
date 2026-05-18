<div align="center">
  <h1>Captive Portal for Linux Routers</h1>
  <p>Enterprise‑grade hotspot solution for Raspberry Pi, OpenWrt, Ubuntu</p>
  <p>
    <img src="https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/Python-3.6+-3776AB?logo=python&logoColor=white" alt="Python">
    <img src="https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white" alt="SQLite">
    <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
    <img src="https://img.shields.io/badge/Platform-Raspberry%20Pi%20%7C%20OpenWrt%20%7C%20Ubuntu-blue" alt="Platform">
  </p>
</div>

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Authentication Methods](#authentication-methods)
- [Quota Management](#quota-management)
- [Admin Panel](#admin-panel)
- [API Endpoints](#api-endpoints)
- [File Structure](#file-structure)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Features

- **Multiple authentication methods** – social login mock, SMS OTP mock (code `123456`), voucher codes, click‑through acceptance.
- **Session & quota management** – time‑based and bandwidth‑based quotas per user, enforced by a Python monitor and `tc`/`iptables`.
- **Admin panel** – manage online users, generate vouchers, change portal settings on the fly.
- **DNS + HTTP redirection** – `dnsmasq` forces all DNS queries to the portal IP; `iptables` redirects ports 80 and 443.
- **Responsive frontend** – works on mobile and desktop, uses FontAwesome icons, pure CSS.
- **Self‑contained** – all files included, no external dependencies except standard Linux packages.
- **Logging** – every login, logout, quota enforcement logged to `logs/portal.log`.

## Requirements

- Linux router with WiFi interface capable of Access Point mode.
- PHP 7.4+ with SQLite3 and CGI support.
- Python 3.6+.
- `hostapd`, `dnsmasq`, `iptables`, `netfilter-persistent` (or equivalent).
- 100 MB free disk space.

## Installation

Clone the repository and run the install script as root:

```bash
git clone https://github.com/yourname/captive-portal.git
cd captive-portal
sudo bash install.sh
```

The script will:
1. Detect your distribution (Debian/Raspbian/Ubuntu or OpenWrt).
2. Install required packages.
3. Configure `hostapd` and `dnsmasq` with the default SSID `CaptivePortal` and IP `192.168.4.1`.
4. Set up `iptables` rules for transparent redirection.
5. Create the SQLite database and schema.
6. Enable IP forwarding and start services.
7. Output the portal IP and default admin credentials (admin / captive123).

To uninstall, run:

```bash
sudo bash uninstall.sh
```

## Authentication Methods

Edit `/etc/captive-portal.ini` and set `auth_method` to one of:

- `social` – mock OAuth login (Facebook/Google). Replace the mock IDs in `config.ini` with real OAuth credentials for production.
- `sms` – SMS OTP mock. Any phone number works with OTP `123456`.
- `click` – simple "Accept Terms" checkbox.
- `voucher` – redeem a voucher code from the `vouchers` table.

Example voucher insertion:

```bash
sqlite3 web/data/users.db "INSERT INTO vouchers (code, used) VALUES ('FREE123', 0);"
```

## Quota Management

Quotas are defined in `config.ini`:

```ini
quota_time_minutes = 120
quota_bandwidth_mb = 100
```

The Python monitor (`scripts/monitor.py`) runs every minute (via cron) and:
- Reads traffic counters from `iptables` for each active session.
- Stores bytes uploaded/downloaded in `usage_log`.
- Kicks the user when either time or bandwidth quota is exceeded.

To set up the cron job manually:

```bash
crontab -e
* * * * * /usr/bin/python3 /var/www/html/captive-portal/scripts/monitor.py
```

## Admin Panel

Access the admin panel at `http://192.168.4.1/admin`.

- **Login** – use the credentials set during installation (default: admin / captive123). The password is stored as a Bcrypt hash in `config.ini`.
- **Dashboard** – view active sessions and disconnect any user.
- **Vouchers** – generate new voucher codes, see used/unused codes.
- **Settings** – change portal name, redirect URL, session timeout, quotas, and authentication method. Also change the admin username and password hash.

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/captive.php?action=getAuthMethod` | GET | Returns current authentication method as JSON. |
| `/captive.php` | POST | Handles login (SMS, click‑through, voucher). |
| `/status.php` | GET | Returns JSON with authentication status and quota usage. |
| `/logout.php` | GET | Destroys the session cookie and deletes the session. |

## File Structure

```
captive-portal/
├── README.md
├── SETUP_SOFTWARE.md
├── SETUP_PHYSICAL_ROUTER.md
├── CONFIGURATION.md
├── TROUBLESHOOTING.md
├── install.sh
├── uninstall.sh
├── config/
│   ├── config.ini
│   ├── dnsmasq.conf.append
│   ├── hostapd.conf.append
│   └── iptables.rules
├── web/
│   ├── index.html
│   ├── style.css
│   ├── script.js
│   ├── captive.php
│   ├── status.php
│   ├── logout.php
│   ├── admin/
│   │   ├── index.php
│   │   ├── auth.php
│   │   ├── users.php
│   │   └── settings.php
│   ├── assets/
│   │   ├── logo.svg
│   │   ├── bg.jpg
│   │   └── favicon.ico
│   └── data/
│       ├── schema.sql
│       └── users.db
├── cgi-bin/
│   ├── redirect.cgi
│   └── splash.cgi
├── scripts/
│   ├── monitor.py
│   ├── mac_auth.sh
│   └── reset_quota.sh
└── logs/
    └── .gitkeep
```

## Configuration

All tunable parameters are in `config/config.ini` (copied to `/etc/captive-portal.ini` during installation).

Key settings:

```ini
[general]
portal_name = Captive Portal
portal_ip = 192.168.4.1
default_redirect = https://google.com
session_timeout_minutes = 60
quota_time_minutes = 120
quota_bandwidth_mb = 100
auth_method = voucher

[admin]
username = admin
password_hash = $2y$10$...

[social]
facebook_app_id = your_id_here
...
```

After changing configuration, restart the Python monitor and reload iptables if needed.

## Troubleshooting

Refer to `TROUBLESHOOTING.md` for common issues. Quick fixes:

- **No redirect** – check iptables: `sudo iptables -t nat -L`. Re-apply with `sudo iptables-restore < config/iptables.rules`.
- **Portal not popping up** – browse to `http://example.com` manually. Disable any VPN or AdBlock.
- **Logs** – all events are written to `logs/portal.log`.
- **Reset all users** – run `sudo bash scripts/reset_quota.sh`.

## License

Mozilla – free for personal and commercial use.




