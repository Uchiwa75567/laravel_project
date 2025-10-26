#!/bin/bash

# Exit on error
set -e

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Swagger docs
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Start Apache
echo "Starting Apache server..."
apache2-foreground
