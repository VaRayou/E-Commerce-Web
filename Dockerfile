# Use official PHP image with Apache web server
FROM php:8.2-apache

# Copy all your project files into Apache's server directory
COPY . /var/www/html/

# Expose HTTP port
EXPOSE 80