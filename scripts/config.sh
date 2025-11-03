#!/bin/bash
# ========================
# Configuración global
# ========================

# Carpetas origen
SOURCE_DIR="/var/www/html"

# Backups locales
LOCAL_BACKUP_DIR="/Draftosaurus/backups"
LOCAL_DB_BACKUP_DIR="/Draftosaurus/dbBackups"

# Backups remotos
REMOTE_USER="backupuser"
REMOTE_HOST="192.168.56.20" #hay que cambiar esto para cada red
REMOTE_DIR="/home/backupuser/backups"

# Fecha para nombres
DATE=$(date +"%Y-%m-%d_%H-%M-%S")

# Log
LOG_FILE="/Draftosaurus/backups/backup.log"
