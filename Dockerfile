FROM php:8.3-apache

# Installer les extensions PHP nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Autoriser Composer à s'exécuter en tant que root (utile dans les containers de dev)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copier les fichiers du projet
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances PHP
RUN composer install --no-interaction --optimize-autoloader

# Donner les permissions appropriées
RUN chown -R www-data:www-data /var/www/html \
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

# Exposer le port 80
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]
