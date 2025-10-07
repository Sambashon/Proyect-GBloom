#!/bin/bash
source /Draftosaurus/scripts/config.sh

echo "=== [MONITOR SECURITY] === $(date)" | tee -a "$LOG_FILE"

# Mostrar los 10 últimos intentos fallidos
grep "Failed password" /var/log/auth.log | tail -n 10 | tee -a "$LOG_FILE"

# Contar intentos por IP
grep "Failed password" /var/log/auth.log | awk '{print $(NF-3)}' | sort | uniq -c | sort -nr | head -n 5 | tee -a "$LOG_FILE"
