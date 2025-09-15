#!/bin/bash
source /Draftosaurus/scripts/config.sh

echo "=== [LOCAL BACKUP] === $(date)" | tee -a "$LOG_FILE"

# Incremental con rsync
INCREMENTAL_DIR="$LOCAL_BACKUP_DIR/incremental"
mkdir -p "$INCREMENTAL_DIR"
rsync -av --delete "$SOURCE_DIR/" "$INCREMENTAL_DIR/" >> "$LOG_FILE" 2>&1

# Backup comprimido con tar
COMPRESSED_FILE="$LOCAL_BACKUP_DIR/backup_$DATE.tar.gz"
tar -czf "$COMPRESSED_FILE" -C "$SOURCE_DIR" . >> "$LOG_FILE" 2>&1

echo "Backups locales generados: $COMPRESSED_FILE" | tee -a "$LOG_FILE"
