#!/bin/bash
source /Draftosaurus/scripts/config.sh

while true; do
    clear
    echo "======================================="
    echo "     📦 Draftosaurus - Admin Menu"
    echo "======================================="
    echo "1. Ver log de backups"
    echo "2. Ver últimos accesos fallidos"
    echo "3. Listar backups locales"
    echo "4. Listar backups remotos"
    echo "5. Ver uso de disco"
    echo "6. Estado de servicios (Apache/MariaDB/SSH)"
    echo "7. Ejecutar backup maestro ahora"
    echo "0. Salir"
    echo "======================================="
    read -p "Seleccione una opción: " opt

    case $opt in
        1)
            echo -e "\n=== LOG DE BACKUPS ==="
            tail -n 50 "$LOG_FILE"
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        2)
            echo -e "\n=== ÚLTIMOS ACCESOS FALLIDOS ==="
            grep "Failed password" /var/log/auth.log | tail -n 15
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        3)
            echo -e "\n=== BACKUPS LOCALES ==="
            ls -lh "$LOCAL_BACKUP_DIR"/backup_*.tar.gz 2>/dev/null || echo "No hay backups locales"
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        4)
            echo -e "\n=== BACKUPS REMOTOS ==="
            ssh "$REMOTE_USER@$REMOTE_HOST" "ls -lh $REMOTE_DIR/backup_*.tar.gz 2>/dev/null" || echo "No se pudo conectar al remoto"
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        5)
            echo -e "\n=== USO DE DISCO ==="
            df -h | grep -E "Filesystem|/var|/Draftosaurus"
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        6)
            echo -e "\n=== ESTADO DE SERVICIOS ==="
            systemctl is-active apache2
            systemctl is-active mariadb
            systemctl is-active ssh
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        7)
            echo -e "\n=== EJECUTANDO BACKUP MAESTRO ==="
            /Draftosaurus/scripts/master_backup.sh
            echo -e "\nBackup maestro ejecutado. Revise el log en $LOG_FILE"
            echo -e "\nPresione [ENTER] para continuar..."
            read
            ;;

        0)
            echo "Saliendo..."
            exit 0
            ;;

        *)
            echo "Opción no válida"
            sleep 1
            ;;
    esac
done
