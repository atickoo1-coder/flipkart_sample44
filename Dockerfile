FROM php:8.2-apache

# Install PDO MySQL extension, MySQL client, and required utilities
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        unzip \
        default-mysql-client \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
