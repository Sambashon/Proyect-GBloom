#!/bin/bash

echo "=== INSTALACIÓN OPTIMIZADA ==="

# 1. Instalar todo en lotes lógicos
echo "1. Instalando paquetes base..."
sudo apt update
sudo apt install git apache2 openssh-server mariadb-server certbot python3-certbot-apache -y

echo "2. Instalando PHP con todos los módulos..."
sudo apt install php php-{cli,common,mysql,pdo,zip,gd,mbstring,curl,xml,bcmath,intl,soap,opcache,readline} -y

# 3. Configuración de servicios
echo "3. Configurando servicios..."
sudo ssh-keygen -t rsa -b 4096 -f /root/.ssh/id_rsa -N "" -q
sudo systemctl enable apache2 ssh mariadb
sudo systemctl start apache2 ssh mariadb

# 4. Clonar y configurar
echo "4. Clonando repositorio..."
cd /tmp
sudo git clone --single-branch --branch Segunda-Entrega-Ariel https://username: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@github.com/Sambashon/Proyect-GBloom.git temp-repo
cd ..

echo "5. Configurando Apache..."
sudo cp /tmp/temp-repo/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
sudo a2enmod rewrite ssl

echo "6. Copiando archivos..."
sudo mkdir -p /etc/letsencrypt/
sudo cp -r /tmp/temp-repo/certs/etc/* /etc/letsencrypt/
sudo cp -r /tmp/temp-repo/www/* /var/www/html/

# 7. Permisos RECOMENDADOS
echo "7. Configurando permisos..."
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 775 /var/www/html
sudo usermod -aG www-data $USER

# Reiniciar apache2
sudo systemctl restart apache2

# Fijar Ip
sudo cp /tmp/temp-repo/apache/interfaces /etc/network/interfaces
sudo systemctl restart networking

# 8. Limpiar
sudo rm -rf /tmp/temp-repo

echo "=== INSTALACIÓN COMPLETADA ==="
echo "Cierra sesión y vuelve a entrar para que los cambios de grupo surtan efecto"