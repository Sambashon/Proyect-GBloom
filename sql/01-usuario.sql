-- Crear usuario gbloomer con permisos desde cualquier host
CREATE USER IF NOT EXISTS 'gbloomer'@'%' IDENTIFIED BY 'goldenblosser';
GRANT select, insert, delete, update ON gbloom_db.* TO 'gbloomer'@'%';
GRANT ALL PRIVILEGES ON gbloom_db.* TO 'gbloomer'@'localhost';

FLUSH PRIVILEGES;