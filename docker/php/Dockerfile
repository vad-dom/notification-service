FROM php:8.4-fpm-bookworm

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y \
        git \
        curl \
        unzip \
        libpq-dev \
        librabbitmq-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/php/init.sh /usr/local/bin/init.sh

RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/init.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]