# Start from plain PHP-Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    curl \
    mariadb-client \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd

# Install Composer (global)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy *your* composer.json and composer.lock INTO Joomla root
# Ensure composer.json and composer.lock exist in the build context
COPY ./docker/composer.json ./composer.json
COPY ./docker/composer.lock ./composer.lock

# Install the dev dependencies (e.g., Codeception, Helper\DbHelper)
RUN composer update --no-interaction --no-scripts --prefer-dist

# Expose Apache port
EXPOSE 80
