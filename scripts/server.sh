#!/bin/bash

echo "=== Bienvenido a Draftosaurus Server ==="
echo "1. Instalar Draftosaurus Server"
echo "2. Rutinas & Backups"
echo "3. Logs de Servidor"

read option

case $option in

1)

echo "=== INSTALACIÓN DRAFTOSAURUS ==="

# 1. Instalar todo en lotes lógicos
echo "1. Instalando paquetes base..."
apt update
apt install sudo apache2 openssh-server mariadb-server certbot python3-certbot-apache curl -y

echo "1.1 Creando usuario admin..."
useradd -m -s /bin/bash goldenadmin
echo "goldenadmin:Drafto123!" | chpasswd
usermod -aG sudo goldenadmin


echo "2. Instalando PHP con todos los módulos..."
mkdir /php
apt install php php-{cli,common,mysql,pdo,zip,gd,mbstring,curl,xml,bcmath,intl,soap,opcache,readline} -y

# Instalar Composer
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

composer require phpmailer/phpmailer --working-dir=/php/

# 3. Configuración de servicios
echo "3. Configurando servicios..."
ssh-keygen -t rsa -b 4096 -f /root/.ssh/id_rsa -N "" -q
systemctl enable apache2 ssh mariadb
systemctl start apache2 ssh mariadb

# 4. Clonar y configurar
echo "4. Clonando repositorio..."
cd /tmp
git clone --single-branch --branch Segunda-Entrega-Ariel https://username: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@github.com/Sambashon/Proyect-GBloom.git temp-repo
cd ..

echo "5. Configurando Apache..."
cp /tmp/temp-repo/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
a2enmod rewrite ssl

echo "6. Copiando archivos..."
mkdir -p /etc/letsencrypt/
cp -r /tmp/temp-repo/certs/etc/* /etc/letsencrypt/
cp -r /tmp/temp-repo/www/* /var/www/html/

# 7. Permisos
echo "7. Configurando permisos..."
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html
usermod -aG www-data $USER

# Reiniciar apache2
systemctl restart apache2

# Fijar Ip
cp /tmp/temp-repo/apache/interfaces /etc/network/interfaces
systemctl restart networking

# Tareas Programadas
mkdir -p /Draftosaurus/backups
mkdir -p /Draftosaurus/scripts
cp /tmp/temp-repo/scripts/backup_db.sh /Draftosaurus/scripts
cp /tmp/temp-repo/scripts/server.sh /Draftosaurus/scripts
echo "0 0 *14/ * 1 /Draftosaurus/scripts/master_backup.sh" | crontab -
# 8. Crear BD
mysql -u root -e "CREATE DATABASE gbloom_db;"
mysql -u root gbloom_db < /tmp/temp-repo/sql/tablas.sql

# 9. Limpiar
rm -rf /tmp/temp-repo

echo "=== INSTALACIÓN COMPLETADA ==="
echo "La contraseña del usuario admin por defecto es 'Drafto123'"
;;

2)

echo -e "\n=== RUTINAS ==="
crontab -l
echo -e "\n=== BACKUPS ==="
ls -1tr /Draftosaurus/backups

;;

3)

echo -e "\n=== APACHE LOGS ==="
tail -f /var/log/apache2/access.log /var/log/apache2/error.log

;;

*)

echo "La opción elegida no es válida, intente de nuevo"

;;

esac