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

# Copy composer files first (to leverage layer caching)
COPY yii-app/composer.json yii-app/composer.lock* ./
RUN composer install --prefer-dist --no-dev --no-scripts --ignore-platform-reqs

# Copy rest of application
COPY yii-app/ /var/www/html

RUN composer install --prefer-dist --no-dev --optimize-autoloader --ignore-platform-reqs

CMD ["php", "yii", "serve", "--docroot=@app/web", "--port=8080"]
