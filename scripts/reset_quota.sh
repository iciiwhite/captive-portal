#!/bin/bash
DB_PATH="/var/www/html/captive-portal/web/data/users.db"
sqlite3 $DB_PATH "DELETE FROM usage_log; DELETE FROM sessions;"
iptables -F FORWARD
echo "Quotas reset at $(date)" >> /var/www/html/captive-portal/logs/portal.log