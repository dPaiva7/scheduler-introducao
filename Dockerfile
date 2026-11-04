FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libssl-dev \
    supervisor \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY skeleton/ /var/www/html/
RUN composer install --no-interaction --prefer-dist --no-progress --optimize-autoloader

COPY supervisor.conf /etc/supervisor/conf.d/scheduler.conf

CMD ["sh", "-c", "composer install --no-interaction --prefer-dist --no-progress --optimize-autoloader && exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf"]
