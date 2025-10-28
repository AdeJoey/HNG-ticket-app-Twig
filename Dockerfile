# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Enable mod_rewrite for .htaccess support
RUN a2enmod rewrite

# Install any needed PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy your application into the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Allow .htaccess to override settings
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
