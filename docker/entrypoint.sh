#!/bin/bash
set -e

cd /var/www

echo "============================================="
echo "  Tenant Service - Container Starting Up"
echo "============================================="

# Install vendor dependencies jika belum ada (bind mount mode)
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "[1/6] Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader --no-dev 2>&1
else
    echo "[1/6] Vendor directory exists, skipping composer install."
fi

# Set permissions
echo "[2/6] Setting storage permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Wait for database
echo "[3/6] Waiting for database to be ready..."
MAX_TRIES=30
TRIES=0
until php -r "
\$host = getenv('DB_HOST') ?: 'db';
\$db   = getenv('DB_DATABASE') ?: 'tenant_service';
\$user = getenv('DB_USERNAME') ?: 'tenant_user';
\$pass = getenv('DB_PASSWORD') ?: 'secret';
try {
    \$pdo = new PDO(\"mysql:host=\$host;port=3306;dbname=\$db\", \$user, \$pass, [PDO::ATTR_TIMEOUT => 3]);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "ERROR: Database did not become ready in time. Exiting."
        exit 1
    fi
    echo "  Database not ready yet (attempt $TRIES/$MAX_TRIES), retrying in 3s..."
    sleep 3
done
echo "  Database is ready!"

# Run migrations
echo "[4/6] Running database migrations..."
php artisan migrate --force 2>&1 || echo "  Migration warning (may already be up to date)"

# Generate Swagger documentation
echo "[5/6] Generating Swagger documentation..."
php artisan l5-swagger:generate 2>&1 || echo "  Swagger generation warning (will be generated on first request)"

# Cache config and routes for performance
echo "[6/6] Caching configuration..."
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true

echo "============================================="
echo "  Tenant Service is ready!"
echo "  API:     http://localhost:8000/api/v1/health"
echo "  Swagger: http://localhost:8000/docs"
echo "  GraphQL: http://localhost:8000/graphql"
echo "============================================="

exec php-fpm
