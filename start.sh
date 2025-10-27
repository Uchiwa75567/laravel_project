#!/bin/bash

# Exit on error
set -e

# If passport keys are provided via environment variables, write them to storage
if [ -n "\$PASSPORT_PRIVATE_KEY" ]; then
	echo "Writing PASSPORT_PRIVATE_KEY to storage/oauth-private.key"
	mkdir -p storage
	printf "%s" "\$PASSPORT_PRIVATE_KEY" > storage/oauth-private.key
	chmod 600 storage/oauth-private.key || true
fi

if [ -n "\$PASSPORT_PUBLIC_KEY" ]; then
	echo "Writing PASSPORT_PUBLIC_KEY to storage/oauth-public.key"
	mkdir -p storage
	printf "%s" "\$PASSPORT_PUBLIC_KEY" > storage/oauth-public.key
	chmod 644 storage/oauth-public.key || true
fi

# Run database migrations (retry loop in case DB isn't ready)
echo "Running database migrations (with retry)..."
tries=0
until php artisan migrate --force; do
	tries=$((tries+1))
	echo "Migration attempt $tries failed, retrying in 5s..."
	if [ "$tries" -ge 12 ]; then
		echo "Migrations failed after $tries attempts. Exiting." >&2
		exit 1
	fi
	sleep 5
done

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
