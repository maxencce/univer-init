#!/bin/bash
set -e

# Créer les dossiers cache et logs si besoin
mkdir -p /var/www/html/var/cache /var/www/html/var/log

# Donner les droits corrects à Symfony (www-data)
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Donner les droits aux dossiers Supervisor
chown -R www-data:www-data /var/log/symfony /var/run

# Démarrer Apache en background
apache2-foreground &

# Démarrer Supervisor en foreground
exec supervisord -n -c /etc/supervisor/supervisord.conf