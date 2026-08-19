#!/usr/bin/env bash
# Arranca el entorno de desarrollo con los límites de subida del proyecto ya
# aplicados (scripts/php/hurioscan.ini), sin tocar el php.ini de la máquina.
#
# PHP_INI_SCAN_DIR se hereda a los procesos hijos, así que el servidor embebido
# que levanta `artisan serve` también los recibe. Pasar los valores con `php -d`
# no funcionaría: esos ajustes no llegan al proceso hijo.
#
# El `:` final conserva el directorio de configuración propio de la instalación
# de PHP; sin él se perderían las extensiones que declare.
#
# Uso: ./scripts/servir-desarrollo.sh   (equivale a `composer dev`)

set -euo pipefail

raiz="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

export PHP_INI_SCAN_DIR="${raiz}/scripts/php:"

echo "Límites de subida aplicados desde scripts/php/hurioscan.ini:"
php -r 'echo "  upload_max_filesize=", ini_get("upload_max_filesize"), "  post_max_size=", ini_get("post_max_size"), PHP_EOL;'

exec composer dev
