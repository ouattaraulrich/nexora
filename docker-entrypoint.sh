#!/bin/bash
set -e

# ============================================================
#  docker-entrypoint.sh — Nexora Railway startup script
#  1. Configure Apache port (Railway uses dynamic PORT)
#  2. Wait for MySQL to be ready
#  3. Initialize database schema if needed
#  4. Start Apache
# ============================================================

PORT="${PORT:-80}"

echo "🚀 Nexora starting on port $PORT..."

# --- 1. Configure Apache to listen on $PORT ---
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-enabled/000-default.conf

# --- 2. Wait for MySQL ---
if [ -n "$DB_HOST" ] && [ -n "$DB_USER" ]; then
    echo "⏳ Waiting for MySQL at $DB_HOST..."
    MAX_TRIES=30
    COUNT=0
    until mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -P"${DB_PORT:-3306}" -e "SELECT 1" &>/dev/null 2>&1; do
        COUNT=$((COUNT+1))
        if [ $COUNT -ge $MAX_TRIES ]; then
            echo "❌ MySQL not reachable after $MAX_TRIES tries. Continuing anyway..."
            break
        fi
        echo "   Attempt $COUNT/$MAX_TRIES — retrying in 2s..."
        sleep 2
    done
    echo "✅ MySQL is ready!"

    # --- 3. Initialize schema if tables don't exist ---
    DB_NAME="${DB_NAME:-railway}"
    TABLE_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -P"${DB_PORT:-3306}" \
        -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" \
        2>/dev/null | tail -1 || echo "0")

    if [ "$TABLE_COUNT" = "0" ] || [ -z "$TABLE_COUNT" ]; then
        echo "📦 Importing database schema..."
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -P"${DB_PORT:-3306}" "$DB_NAME" \
            < /var/www/html/database/database.sql && echo "✅ Schema imported!" \
            || echo "⚠️  Schema import failed — check logs"
    else
        echo "✅ Database already initialized ($TABLE_COUNT tables found)"
    fi
fi

# --- 4. Start Apache ---
echo "🌐 Starting Apache on port $PORT..."
exec "$@"
