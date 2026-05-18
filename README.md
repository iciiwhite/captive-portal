# Captive Portal for Linux Router

Complete captive portal solution for Raspberry Pi, OpenWrt, Ubuntu. Supports social login mock, SMS OTP mock, voucher codes, click-through. Bandwidth and time quotas.

## Requirements

- Linux router (Raspberry Pi, OpenWrt, Ubuntu 20.04+)
- WiFi interface capable of AP mode
- PHP 7.4+ with SQLite3
- Python 3
- hostapd, dnsmasq, iptables

## Quick Start

```bash
git clone https://github.com/iciiwhite/captive-portal.git
cd captive-portal
sudo bash install.sh
```

Follow on-screen instructions. Default admin: admin / captive123

## License

MIT