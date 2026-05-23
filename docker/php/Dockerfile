FROM dunglas/frankenphp:1.12.0-php8.5

ENV COMPOSER_HOME=/composer
ENV PATH="${COMPOSER_HOME}/vendor/bin:${PATH}"

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    intl \
    zip \
    bcmath \
    opcache \
    pcntl \
    redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /backend
