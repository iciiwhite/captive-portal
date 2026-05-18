#!/bin/bash
set -e

if [ "$EUID" -ne 0 ]; then
    echo "Please run as root"
    exit 1
fi

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PORTAL_IP="192.168.4.1"
IFACE="wlan0"

if [ -f /etc/openwrt_release ]; then
    echo "OpenWrt detected"
    opkg update
    opkg install hostapd dnsmasq php7 php7-cgi php7-mod-sqlite3 sqlite3-cli iptables python3
    UHTTPD_CONF="/etc/config/uhttpd"
    sed -i "s/list listen_http '0.0.0.0:80'/list listen_http '${PORTAL_IP}:80'/" $UHTTPD_CONF
    /etc/init.d/uhttpd restart
else
    apt update
    apt install -y hostapd dnsmasq php php-sqlite3 php-cgi sqlite3 iptables python3 netfilter-persistent iptables-persistent
    systemctl unmask hostapd
    a2enmod cgi
    systemctl restart apache2
fi

mkdir -p /var/www/html/captive-portal
cp -r $DIR/web /var/www/html/captive-portal/
cp -r $DIR/cgi-bin /var/www/html/
chmod +x /var/www/html/cgi-bin/*.cgi
cp $DIR/config/config.ini /etc/captive-portal.ini
cp $DIR/scripts/monitor.py /usr/local/bin/
cp $DIR/scripts/mac_auth.sh /usr/local/bin/
cp $DIR/scripts/reset_quota.sh /usr/local/bin/
chmod +x /usr/local/bin/*.py /usr/local/bin/*.sh

cat $DIR/config/dnsmasq.conf.append >> /etc/dnsmasq.conf
cat $DIR/config/hostapd.conf.append >> /etc/hostapd/hostapd.conf

echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
sysctl -p

iptables-restore < $DIR/config/iptables.rules
if command -v netfilter-persistent >/dev/null; then
    netfilter-persistent save
else
    iptables-save > /etc/iptables/rules.v4
fi

SQLITE_DB="/var/www/html/captive-portal/web/data/users.db"
sqlite3 $SQLITE_DB < $DIR/web/data/schema.sql
ADMIN_HASH=$(php -r "echo password_hash('captive123', PASSWORD_BCRYPT);")
sqlite3 $SQLITE_DB "INSERT INTO admin_settings (username, password_hash) VALUES ('admin', '$ADMIN_HASH');"
chown -R www-data:www-data /var/www/html/captive-portal/web/data
chmod 755 /var/www/html/captive-portal/web/data

systemctl enable hostapd dnsmasq
systemctl restart hostapd dnsmasq

echo "Installation complete. Portal IP: $PORTAL_IP"
echo "Admin: admin / captive123"