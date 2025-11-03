#!/bin/bash
# ========================
# Configuración global para Docker
# ========================

# Docker containers
WEB_CONTAINER="apache_web"
DB_CONTAINER="gbloom_db"

# Database credentials from docker-compose
DB_USER="gbloomer"
DB_PASS="goldenblosser"
DB_NAME="gbloom_db"

# Carpetas origen (dentro del contenedor)
SOURCE_DIR="/var/www/html"

# Backups locales (fuera de los contenedores)
LOCAL_BACKUP_DIR="./backups"
LOCAL_DB_BACKUP_DIR="./dbBackups"

# Backups remotos
REMOTE_USER="backupuser"
REMOTE_HOST="192.168.56.20" #hay que cambiar esto para cada red
REMOTE_DIR="/home/backupuser/backups"

# Fecha para nombres
DATE=$(date +"%Y-%m-%d_%H-%M-%S")

# Log
LOG_FILE="./backups/backup.log"

# Crear directorios si no existen
mkdir -p "$LOCAL_BACKUP_DIR" "$LOCAL_DB_BACKUP_DIR"
