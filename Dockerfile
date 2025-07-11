# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy your PHP website files to the web root
COPY . /var/www/html/

# Expose port 80 (default Apache port)
EXPOSE 80

# Start Apache server (this is the default command in this image)
CMD ["apache2-foreground"]
