FROM php:8.2-apache

# Install build tools and APCu
RUN apt-get update && apt-get install -y \
    git \
    gcc \
    make \
    autoconf \
    pkg-config \
    libpcre3-dev \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && echo "apc.enable_cli=1" > /usr/local/etc/php/conf.d/apcu.ini \
    && apt-get clean && rm -rf /var/lib/apt/lists/*