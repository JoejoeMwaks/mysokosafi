# Use official PHP + Apache image
FROM php:8.2-apache

# Install required dependencies for Composer
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your project into Apache web root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install PHP dependencies (Cloudinary SDK)
RUN composer install --no-dev --optimize-autoloader

# Enable URL rewrite rules
RUN a2enmod rewrite

# Install required MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Create an entrypoint script to force MPM prefork and bind to Railway's dynamic PORT
# Also ensures the images directory is writable by Apache
RUN echo '#!/bin/bash\n\
    a2dismod mpm_event mpm_worker || true\n\
    a2enmod mpm_prefork\n\
    sed -i "s/Listen 80/Listen ${PORT:-80}/g" /etc/apache2/ports.conf\n\
    sed -i "s/:80/:${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf\n\
    mkdir -p /var/www/html/assets/images/products\n\
    chown -R www-data:www-data /var/www/html/assets/images/products\n\
    chmod -R 775 /var/www/html/assets/images/products\n\
    exec apache2-foreground' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080 80

CMD ["/usr/local/bin/entrypoint.sh"]