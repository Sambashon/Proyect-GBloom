#!/bin/bash
source ./config.sh

# Crear directorio local si no existe
mkdir -p "$LOCAL_DB_BACKUP_DIR"

# Máximo número de backups a conservar
maximo=5

# Nombre del archivo de backup con fecha
DB_BACKUP_FILE="$LOCAL_DB_BACKUP_DIR/backup-$(date +%Y-%m-%d-%H-%M).sql.gz"

echo "=== [DB BACKUP] === $(date)" | tee -a "$LOG_FILE"

# Hacer backup de la base de datos desde el contenedor de MariaDB y comprimir
docker exec $DB_CONTAINER sh -c "mysqldump -u$DB_USER -p$DB_PASS $DB_NAME" | gzip -q > "$DB_BACKUP_FILE"

# --- LIMPIAR BACKUPS LOCALES ---
# Listar backups locales ordenados de más antiguo a más reciente
archivos_locales=($(ls -1tr "$LOCAL_DB_BACKUP_DIR"/*.sql.gz 2>/dev/null))

# Eliminar los más antiguos si exceden el límite
if [ ${#archivos_locales[@]} -gt $maximo ]; then
    for ((i=0; i<${#archivos_locales[@]}-maximo; i++)); do
        rm -f "${archivos_locales[$i]}"
    done
fi

# --- BACKUP REMOTO ---
echo "Subiendo backup al servidor remoto $REMOTE_HOST..." | tee -a "$LOG_FILE"
scp "$DB_BACKUP_FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/"

# Limpiar backups remotos antiguos
echo "Limpiando backups remotos antiguos..." | tee -a "$LOG_FILE"
ssh "$REMOTE_USER@$REMOTE_HOST" "ls -1tr $REMOTE_DIR/backup-*.sql.gz 2>/dev/null | head -n -$maximo | xargs -r rm -f"

echo "Backup completado." | tee -a "$LOG_FILE"
