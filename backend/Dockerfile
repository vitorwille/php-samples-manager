FROM php:8.5-cli-alpine

RUN apk add --no-cache curl entr autoconf g++ make \
    && docker-php-ext-install pdo_mysql \
    && pecl install pcov && docker-php-ext-enable pcov \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .
RUN composer dump-autoload --no-dev --no-interaction \
    && chmod +x /app/entrypoint.sh

EXPOSE 8080
CMD ["/app/entrypoint.sh"]
