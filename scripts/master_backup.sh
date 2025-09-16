#!/bin/bash
source /Draftosaurus/scripts/config.sh

echo "=== [MASTER BACKUP RUN] === $(date)" | tee -a "$LOG_FILE"

# 1. Copias locales
/Draftosaurus/scripts/backup_local.sh

# 2. Copias remotas
/Draftosaurus/scripts/backup_remote.sh

# 3. Monitoreo de accesos fallidos
/Draftosaurus/scripts/monitor_security.sh

# 4. Backup de base de datos
/Draftosaurus/scripts/backup_db.sh
/Draftosaurus/scripts/backup_encrypted_db.sh
echo "=== [END RUN] === $(date)" | tee -a "$LOG_FILE"
