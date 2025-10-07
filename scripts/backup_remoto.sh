#!/bin/bash
source /Draftosaurus/scripts/config.sh

echo "=== [REMOTE BACKUP] === $(date)" | tee -a "$LOG_FILE"

# Enviar el backup comprimido más reciente con SCP
LATEST_BACKUP=$(ls -1t "$LOCAL_BACKUP_DIR"/backup_*.tar.gz | head -n 1)
scp "$LATEST_BACKUP" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/" >> "$LOG_FILE" 2>&1

# Rsync incremental hacia la otra VM
rsync -av --delete "$SOURCE_DIR/" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/incremental/" >> "$LOG_FILE" 2>&1

echo "Copias remotas completadas en $REMOTE_HOST:$REMOTE_DIR" | tee -a "$LOG_FILE"
