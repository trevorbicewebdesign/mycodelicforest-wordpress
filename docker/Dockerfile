# Start from plain PHP-Apache
FROM php:8.2-apache

# System deps
RUN apt-get update && apt-get install -y \
    unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    git curl mariadb-client \
 && docker-php-ext-install mysqli pdo pdo_mysql zip gd \
 && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite and allow .htaccess in /var/www/html
RUN a2enmod rewrite \
 && printf '<Directory "/var/www/html">\n    AllowOverride All\n</Directory>\n' \
    > /etc/apache2/conf-available/wordpress.conf \
 && a2enconf wordpress

# Optional: set ServerName to avoid noisy warning
RUN printf "ServerName localhost\n" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Work inside the web root
WORKDIR /var/www/html

# Copy your project composer files from repo root's docker/ folder
COPY docker/composer.json composer.json
COPY docker/composer.lock composer.lock

# Install deps according to the lockfile
RUN composer install --no-interaction --no-progress --prefer-dist

# Optional: seed a sane default WP .htaccess (CI-friendly)
RUN printf '%s\n' \
  '# BEGIN WordPress' \
  '<IfModule mod_rewrite.c>' \
  'RewriteEngine On' \
  'RewriteBase /' \
  'RewriteRule ^index\.php$ - [L]' \
  'RewriteCond %{REQUEST_FILENAME} !-f' \
  'RewriteCond %{REQUEST_FILENAME} !-d' \
  'RewriteRule . /index.php [L]' \
  '</IfModule>' \
  '# END WordPress' > /var/www/html/.htaccess \
 && chown www-data:www-data /var/www/html/.htaccess

EXPOSE 80
