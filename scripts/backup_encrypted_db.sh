#!/bin/bash
source /Draftosaurus/scripts/config.sh

# Crear directorio local si no existe
mkdir -p "$LOCAL_DB_BACKUP_DIR"

# Máximo número de backups a conservar
maximo=5

# Nombre del archivo de backup con fecha
DB_BACKUP_FILE="$LOCAL_DB_BACKUP_DIR/backup-$(date +%Y-%m-%d-%H-%M).sql.gz"
DB_BACKUP_ENC_FILE="${DB_BACKUP_FILE}.enc"

echo "=== [DB BACKUP] === $(date)" | tee -a "$LOG_FILE"

# Hacer backup de la base de datos, comprimir y cifrar con OpenSSL
mysqldump -u root gbloom_db \
  | gzip -q \
  | openssl enc -aes-256-cbc -pbkdf2 -salt -pass pass:"$BACKUP_PASSPHRASE" \
  -out "$DB_BACKUP_ENC_FILE"

# --- LIMPIAR BACKUPS LOCALES ---
# Listar backups locales ordenados de más antiguo a más reciente
archivos_locales=($(ls -1tr "$LOCAL_DB_BACKUP_DIR"/*.sql.gz.enc 2>/dev/null))

# Eliminar los más antiguos si exceden el límite
if [ ${#archivos_locales[@]} -gt $maximo ]; then
    for ((i=0; i<${#archivos_locales[@]}-maximo; i++)); do
        rm -f "${archivos_locales[$i]}"
    done
fi

# --- BACKUP REMOTO ---
echo "Subiendo backup al servidor remoto $REMOTE_HOST..." | tee -a "$LOG_FILE"
scp "$DB_BACKUP_ENC_FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/"

# Limpiar backups remotos antiguos
echo "Limpiando backups remotos antiguos..." | tee -a "$LOG_FILE"
ssh "$REMOTE_USER@$REMOTE_HOST" "ls -1tr $REMOTE_DIR/backup-*.sql.gz.enc 2>/dev/null | head -n -$maximo | xargs -r rm -f"

echo "Backup completado." | tee -a "$LOG_FILE"
