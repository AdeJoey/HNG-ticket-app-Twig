# Use official PHP image with Apache
FROM php:8.2-apache

# Enable mod_rewrite for pretty URLs
RUN a2enmod rewrite

# Install required PHP extensions (optional)
RUN docker-php-ext-install pdo pdo_mysql

# Copy your app files to the Apache web root
COPY . /var/www/html/

# Fix permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Allow .htaccess to work
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
