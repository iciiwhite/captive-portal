#!/bin/bash
MAC=$1
ALLOWED_FILE="/etc/captive_allowed_macs"
echo "$MAC" >> $ALLOWED_FILE
iptables -t nat -I PREROUTING -m mac --mac-source $MAC -j ACCEPT