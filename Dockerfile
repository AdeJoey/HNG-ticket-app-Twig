# Use official PHP Apache image
FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev unzip git \
    && docker-php-ext-install intl zip

# Install Composer (copy from composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory and copy project
WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP dependencies (composer)
RUN composer install --no-dev --no-interaction --optimize-autoloader || true

# Ensure public/ is Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/sites-available/default-ssl.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

EXPOSE 80
CMD ["apache2-foreground"]
