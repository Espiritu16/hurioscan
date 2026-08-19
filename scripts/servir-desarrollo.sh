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
# Uso: ./scripts/servir-desarrollo.sh
#
# Este es el arranque soportado del entorno de desarrollo, y NO es intercambiable
# con `composer dev`. Arrancar con `composer dev` o `php artisan serve` directos
# deja los límites en los valores por defecto de la máquina (medido: 2M y 20
# archivos), y el recorte es silencioso: la aplicación no recibe ninguna señal de
# que le truncaron el lote, que es justamente el defecto QA-F-03. Solo este
# script exporta PHP_INI_SCAN_DIR, que es lo que hace que scripts/php/hurioscan.ini
# se aplique.

set -euo pipefail

raiz="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

export PHP_INI_SCAN_DIR="${raiz}/scripts/php:"

echo "Límites de subida aplicados desde scripts/php/hurioscan.ini:"
php -r 'echo "  upload_max_filesize=", ini_get("upload_max_filesize"), "  post_max_size=", ini_get("post_max_size"), "  max_file_uploads=", ini_get("max_file_uploads"), PHP_EOL;'

exec composer dev
