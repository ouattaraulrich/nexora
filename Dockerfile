# syntax=docker/dockerfile:1
FROM php:8.2-apache

# Extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libwebp-dev libzip-dev unzip curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour .htaccess
RUN a2enmod rewrite headers expires deflate

# Config Apache : AllowOverride pour .htaccess
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copier les fichiers du projet
WORKDIR /var/www/html
COPY . .

# Créer dossier uploads avec les bons droits
RUN mkdir -p uploads && chown -R www-data:www-data uploads && chmod 775 uploads

# PHP config pour la production
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/php.ini

# Port exposé par Railway/Render (Apache écoute sur 80 par défaut)
EXPOSE 80

CMD ["apache2-foreground"]
