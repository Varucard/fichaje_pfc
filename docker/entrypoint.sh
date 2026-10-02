#!/bin/sh
# Asegura que Apache pueda escribir logs y respaldos, incluso cuando el
# proyecto se monta como volumen desde la máquina de desarrollo.
set -e
mkdir -p storage/logs storage/backups storage/emails
chmod -R a+rwX storage 2>/dev/null || true
exec "$@"
