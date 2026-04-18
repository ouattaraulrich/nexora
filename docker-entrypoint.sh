#!/bin/bash
set -e

# ============================================================
#  docker-entrypoint.sh — Nexora Railway
# ============================================================

PORT="${PORT:-80}"
echo "🚀 Application Nexora démarre sur le port $PORT..."

# --- 1. Configuration du port Apache ---
# On remplace 80 par le port Railway dans toute la config Apache
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf

# --- 2. Initialisation DB en arrière-plan (pour ne pas bloquer Apache) ---
(
    if [ -z "$DB_HOST" ]; then
        echo "⚠️  DB_HOST non défini. L'initialisation de la base de données est sautée."
    else
        echo "⏳ Tentative de connexion à la base de données ($DB_HOST)..."
        # On attend maximum 10 secondes pour ne pas faire échouer le healthcheck
        MAX_TRIES=5
        for i in $(seq 1 $MAX_TRIES); do
            if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -P"${DB_PORT:-3306}" -e "SELECT 1" >/dev/null 2>&1; then
                echo "✅ Connexion DB réussie !"
                
                # Import du schéma si vide
                DB_NAME="${DB_NAME:-railway}"
                TABLE_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -P"${DB_PORT:-3306}" -N -s -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';")
                
                if [ "$TABLE_COUNT" -eq "0" ]; then
                    echo "📦 Importation du schéma SQL..."
                    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -P"${DB_PORT:-3306}" "$DB_NAME" < /var/www/html/database/database.sql
                    echo "✅ Schéma importé avec succès."
                fi
                break
            fi
            echo "   [Tentative $i/$MAX_TRIES] DB non prête, on réessaie dans 2s..."
            sleep 2
        done
    fi
) &

# --- 3. Lancement d'Apache ---
echo "🌐 Lancement du serveur Web..."
exec "$@"
