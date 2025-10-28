# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Enable Apache modules for URL rewriting and .htaccess support
RUN a2enmod rewrite

# Install system dependencies and Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy all project files to container
COPY . /var/www/html/

# Fix permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install PHP dependencies via Composer (including Twig)
RUN composer install --no-dev --optimize-autoloader || true

# Allow .htaccess overrides and public access
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Expose web port
EXPOSE 80

# Start Apache
Index of /
[ICO]	Name	Last modified	Size	Description
[   ]	Dockerfile	2025-10-28 18:15	1.2K	 
[TXT]	README.md	2025-10-28 18:15	897	 
[   ]	composer.json	2025-10-28 18:15	214	 
[   ]	composer.lock	2025-10-28 18:15	11K	 
[DIR]	data/	2025-10-28 18:15	-	 
[DIR]	public/	2025-10-28 18:15	-	 
[DIR]	src/	2025-10-28 18:15	-	 
[DIR]	vendor/	2025-10-28 18:15	-	 
Apache/2.4.65 (Debian) Server at hng-ticket-app-twig-233e.onrender.com Port 80
CMD ["apache2-foreground"]
