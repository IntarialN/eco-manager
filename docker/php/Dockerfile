FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install Yii tool
RUN composer global require "fxp/composer-asset-plugin:^1.4.4"

ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Copy application code first (for caching composer deps)
COPY yii-app/composer.json yii-app/composer.lock* ./
RUN composer install --no-scripts --no-autoloader || true

# Copy rest of application
COPY yii-app/ /var/www/html

RUN composer install --prefer-dist --no-dev --optimize-autoloader

CMD ["php", "yii", "serve", "--docroot=@app/web", "--port=8080"]
