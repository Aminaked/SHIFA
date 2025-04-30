FROM php:8.2-cli

# Sync container time
RUN apt-get update -o Acquire::Check-Valid-Until=false \
    && apt-get install -y \
        libzip-dev \
        unzip \
        libpq-dev \
        cron \
        libcurl4-openssl-dev \
    && docker-php-ext-install zip pdo pdo_mysql curl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /inventory-sync

# Copy application code
COPY . .

# Setup cron
RUN echo "0 * * * * root /usr/local/bin/php /inventory-sync/PharmacySync.php" > /etc/cron.d/sync-cron
RUN chmod 0644 /etc/cron.d/sync-cron

CMD ["cron", "-f"]
