#!/bin/bash
# ============================================
# Script de respaldo de base de datos MariaDB
# Host Debian → Contenedor MariaDB (localhost:3306)
# Variables cargadas desde .env (del proyecto Docker)
# ============================================

# Ruta al archivo .env (ajustala si está en otra carpeta)
PROJECT="Proyect-GBloom"
ENV_FILE="/home/$USER/$PROJECT/.env"

# Directorio donde se guardarán los respaldos
BACKUP_DIR="/home/$USER/backups"

# Archivo de log
LOG_FILE="${BACKUP_DIR}/backup.log"

# ============================================

# Verificar que el archivo .env existe
if [ ! -f "$ENV_FILE" ]; then
  echo "$(date '+%Y-%m-%d %H:%M:%S') - ERROR: No se encontró el archivo .env en $ENV_FILE" >> $LOG_FILE
  exit 1
fi

# Cargar variables del .env
export $(grep -v '^#' "$ENV_FILE" | xargs)

# Variables esperadas en .env:
# DB_HOST, DB_USER, DB_PASS, DB_NAME

# Si alguna variable falta, salir con error
if [ -z "$DB_HOST" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ] || [ -z "$DB_NAME" ]; then
  echo "$(date '+%Y-%m-%d %H:%M:%S') - ERROR: Faltan variables requeridas en .env (DB_HOST, DB_USER, DB_PASS, DB_NAME)" >> "$LOG_FILE"
  exit 1
fi

# Crear carpeta de respaldo si no existe
mkdir -p "$BACKUP_DIR"

# Nombre del archivo de respaldo
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${DATE}.sql.gz"
echo "VARIABLES"
echo "DB_HOST=$DB_HOST, $DB_USER, $DB_PASS, $DB_NAME"
# Ejecutar respaldo desde el host hacia el contenedor
#echo 'mysqldump -h "$DB_HOST" -P 3306 -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>>"$LOG_FILE" | gzip > "$BACKUP_FILE"'
mysqldump -h "$DB_HOST" -P 3306 -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>>"$LOG_FILE" | gzip > "$BACKUP_FILE"

# Verificar resultado
if [ $? -eq 0 ]; then
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Respaldo exitoso: $BACKUP_FILE" >> "$LOG_FILE"
else
  echo "$(date '+%Y-%m-%d %H:%M:%S') - ERROR al generar el respaldo de $DB_NAME" >> "$LOG_FILE"
  exit 1
fi

# Política de retención (7 días)
find "$BACKUP_DIR" -type f -mtime +7 -name "*.sql.gz" -exec rm {} \;

echo "$(date '+%Y-%m-%d %H:%M:%S') - Limpieza de respaldos antiguos completada." >> "$LOG_FILE"
exit 0


# Limpiar backups remotos antiguos
#echo "Limpiando backups remotos antiguos..." | tee -a "$LOG_FILE"
#ssh "$REMOTE_USER@$REMOTE_HOST" "ls -1tr $REMOTE_DIR/backup-*.sql.gz 2>/dev/null | head -n -$maximo | xargs -r rm -f"
echo "Backup completado." | tee -a "$LOG_FILE"
