#!/bin/bash
source ./config.sh

echo "=== [LOCAL BACKUP] === $(date)" | tee -a "$LOG_FILE"

# Incremental con rsync desde el contenedor
INCREMENTAL_DIR="$LOCAL_BACKUP_DIR/incremental"
mkdir -p "$INCREMENTAL_DIR"
docker cp $WEB_CONTAINER:$SOURCE_DIR/. "$INCREMENTAL_DIR/" >> "$LOG_FILE" 2>&1

# Backup comprimido con tar desde el contenedor
COMPRESSED_FILE="$LOCAL_BACKUP_DIR/backup_$DATE.tar.gz"
docker exec $WEB_CONTAINER tar -czf - -C $SOURCE_DIR . > "$COMPRESSED_FILE" 2>> "$LOG_FILE"

echo "Backups locales generados: $COMPRESSED_FILE" | tee -a "$LOG_FILE"
