# Troubleshooting

## No Redirect

Check iptables rules:

```bash
sudo iptables -t nat -L
```

Should show REDIRECT for ports 80 and 443 to portal IP.

Apply rules:

```bash
sudo iptables-restore < config/iptables.rules
```

## DNS Loop

Ensure dnsmasq is not forwarding to itself. Set `server=` to upstream DNS like 8.8.8.8 in `/etc/dnsmasq.conf`.

## Captive Portal Not Popping Up

On iOS/Android, browse to http://example.com. Check if portal responds:

```bash
curl -I http://192.168.4.1
```

Disable AdBlock.

## iptables Rules Lost After Reboot

Save:

```bash
sudo netfilter-persistent save
sudo systemctl enable netfilter-persistent
```

## Logs Location

All actions logged to `logs/portal.log`.

## Reset Users

Clear sessions:

```bash
sqlite3 web/data/users.db "DELETE FROM sessions;"
```

Reset quotas:

```bash
sudo bash scripts/reset_quota.sh
```

## Python Monitor Not Running

Start manually:

```bash
sudo python3 scripts/monitor.py &
```

Add to crontab every minute:

```bash
* * * * * /usr/bin/python3 /path/to/scripts/monitor.py
```

## Admin Login Failed

Regenerate password hash in `config.ini`.