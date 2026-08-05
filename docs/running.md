git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan filament:upgrade
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache