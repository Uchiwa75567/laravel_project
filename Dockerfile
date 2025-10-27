FROM php:8.3-apache

# Installer les extensions PHP nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    gnupg \
    ca-certificates \
    build-essential \
    && docker-php-ext-install pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Autoriser Composer à s'exécuter en tant que root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copier les fichiers du projet
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances PHP selon l'environnement
# Install Composer dependencies (production build - no dev deps)
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts || true

# Install Node.js (used by Vite) and build front-end assets if package.json is present
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && if [ -f package.json ]; then \
        npm ci --silent || npm install --silent; \
        npm run build --silent || true; \
    fi

# Run Composer scripts (post-install) after assets are built
RUN composer run-script post-autoload-dump --no-interaction || true

# Donner les permissions appropriées
RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/storage/framework /var/www/html/bootstrap/cache || true \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Activer le module rewrite d'Apache
RUN a2enmod rewrite

# Configurer Apache pour Laravel
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copier le script de démarrage
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Exposer le port 80
EXPOSE 80

# Commande par défaut selon l'environnement
CMD if [ "$APP_ENV" = "production" ]; then /usr/local/bin/start.sh; else apache2-foreground; fi
