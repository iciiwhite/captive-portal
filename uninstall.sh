#!/bin/bash
set -e
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root"
    exit 1
fi

rm -rf /var/www/html/captive-portal
rm -rf /var/www/html/cgi-bin/redirect.cgi /var/www/html/cgi-bin/splash.cgi
rm -f /etc/captive-portal.ini
rm -f /usr/local/bin/monitor.py /usr/local/bin/mac_auth.sh /usr/local/bin/reset_quota.sh

sed -i '/dhcp-range=192.168.4/d' /etc/dnsmasq.conf
sed -i '/interface=wlan0/d' /etc/hostapd/hostapd.conf
sed -i '/ssid=/d' /etc/hostapd/hostapd.conf

iptables -t nat -F
iptables -F
if command -v netfilter-persistent >/dev/null; then
    netfilter-persistent save
fi

systemctl disable hostapd dnsmasq
systemctl stop hostapd dnsmasq

echo "Uninstall completed"