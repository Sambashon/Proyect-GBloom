#!/bin/bash

maximo=5

mysqldump -u root -p --all-databases | gzip -q > "$carpeta/backup-$(date +%Y-%m-%d-%H-%M).sql.gz"

# Obtiene la lista de backups en orden invertido de fecha de modificación (orden de viejo a nuevo)
archivos=($(ls -1tr /Draftosaurus/backups/*.sql.gz))

# Itera sobre los archivos más viejos (los primeros), basandose en la cantidad de backups existentes
if [ ${#archivos[@]} -gt $maximo ]; then
    for ((i=0; i<${#archivos[@]}-maximo; i++)); do
        rm -f "${archivos[$i]}"
    done
fi