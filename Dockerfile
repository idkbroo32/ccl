FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 10000
CMD ["sh", "-c", "PORT=\"${PORT:-10000}\"; sed -i -E \"s/^Listen .*/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -i -E \"s#<VirtualHost [*]:[0-9]+>#<VirtualHost *:${PORT}>#\" /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
