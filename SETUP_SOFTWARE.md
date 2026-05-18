# Software Installation

This document covers manual software setup. Install script does this automatically.

## Debian / Ubuntu / Raspberry Pi OS

Update system:

```bash
sudo apt update && sudo apt upgrade -y
```

Install required packages:

```bash
sudo apt install -y hostapd dnsmasq php php-sqlite3 php-cgi sqlite3 iptables python3 python3-pip netfilter-persistent iptables-persistent
sudo systemctl unmask hostapd
sudo systemctl enable hostapd dnsmasq
```

## OpenWrt

Use opkg:

```bash
opkg update
opkg install hostapd dnsmasq php7 php7-cgi php7-mod-sqlite3 sqlite3-cli iptables python3
```

## Verify PHP

```bash
php -v
```

## Setup SQLite

```bash
sqlite3 /var/www/captive-portal/web/data/users.db < web/data/schema.sql
chown www-data:www-data /var/www/captive-portal/web/data/users.db
```

## Start Services

```bash
sudo systemctl restart hostapd dnsmasq
sudo netfilter-persistent save
```

## Enable IP Forwarding

```bash
echo 'net.ipv4.ip_forward=1' | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```