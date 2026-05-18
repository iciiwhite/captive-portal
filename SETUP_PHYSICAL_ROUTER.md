# Physical Router Setup

## Hardware Requirements

- Raspberry Pi 3/4/Zero W or any Linux device with WiFi
- Ethernet cable for WAN uplink
- MicroSD card (8GB+)
- Power supply

## Flashing OpenWrt

Download image from openwrt.org. Use balenaEtcher to flash to SD card. Insert into device, power on. Connect via LAN port to computer, access 192.168.1.1.

## Raspberry Pi as Access Point

Use Raspberry Pi OS Lite. Connect Pi to internet via Ethernet. Then:

Configure static IP on wlan0:

```bash
sudo cat > /etc/dhcpcd.conf << EOF
interface wlan0
    static ip_address=192.168.4.1/24
    nohook wpa_supplicant
EOF
```

Enable AP mode via install.sh.

## Cabling

- Ethernet port (eth0) → Internet router LAN port (WAN side)
- WiFi (wlan0) → clients

## Disable Upstream DHCP

Set your main router’s DHCP range to exclude 192.168.4.x or connect the portal router's WAN to main router's LAN and let portal manage its own subnet.

## Antenna Placement

Place router centrally, avoid metal obstacles. For RPi, use external antenna if available.

## Test

```bash
ip addr show wlan0
```

Should show 192.168.4.1.