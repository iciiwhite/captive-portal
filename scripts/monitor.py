#!/usr/bin/env python3
import sqlite3
import time
import subprocess
import os
from datetime import datetime

CONFIG_FILE = '/etc/captive-portal.ini'
DB_PATH = '/var/www/html/captive-portal/web/data/users.db'
LOG_FILE = '/var/www/html/captive-portal/logs/portal.log'

def get_config():
    import configparser
    cp = configparser.ConfigParser()
    cp.read(CONFIG_FILE)
    return cp

def log(msg):
    with open(LOG_FILE, 'a') as f:
        f.write(f"{datetime.now()} - {msg}\n")

def get_iptables_bytes(ip):
    try:
        cmd = f"iptables -L FORWARD -v -n -x | grep {ip} | awk '{{print $2, $8}}'"
        out = subprocess.check_output(cmd, shell=True, text=True)
        parts = out.split()
        if len(parts) >= 2:
            return int(parts[0]), int(parts[1])
    except:
        pass
    return 0, 0

def update_usage():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT id, ip FROM sessions WHERE expires > datetime('now')")
    sessions = c.fetchall()
    for sid, ip in sessions:
        up, down = get_iptables_bytes(ip)
        c.execute("INSERT INTO usage_log (session_id, bytes_up, bytes_down) VALUES (?, ?, ?)", (sid, up, down))
    conn.commit()
    conn.close()

def enforce_quotas():
    config = get_config()
    quota_mb = int(config['general']['quota_bandwidth_mb'])
    quota_min = int(config['general']['quota_time_minutes'])
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT id, mac, ip, login_time FROM sessions WHERE expires > datetime('now')")
    sessions = c.fetchall()
    for sid, mac, ip, login_time in sessions:
        c.execute("SELECT SUM(bytes_up + bytes_down) FROM usage_log WHERE session_id = ?", (sid,))
        total_bytes = c.fetchone()[0] or 0
        total_mb = total_bytes / (1024*1024)
        login_ts = datetime.strptime(login_time, '%Y-%m-%d %H:%M:%S')
        minutes_used = (datetime.now() - login_ts).total_seconds() / 60
        if total_mb > quota_mb or minutes_used > quota_min:
            subprocess.run(f"iptables -D FORWARD -s {ip} -j ACCEPT 2>/dev/null", shell=True)
            c.execute("DELETE FROM sessions WHERE id = ?", (sid,))
            log(f"Quota exceeded for {mac} {ip}")
    conn.commit()
    conn.close()

if __name__ == '__main__':
    update_usage()
    enforce_quotas()