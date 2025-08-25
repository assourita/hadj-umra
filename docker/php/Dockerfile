FROM php:8.2-fpm

# Arguments
ARG WORKDIR=/var/www/html

# Update packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR $WORKDIR

# Copy application files
COPY . $WORKDIR

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data $WORKDIR \
    && chmod -R 755 $WORKDIR/var \
    && chmod -R 755 $WORKDIR/public

# Create upload directories
RUN mkdir -p public/uploads/documents \
    && mkdir -p public/uploads/images \
    && chown -R www-data:www-data public/uploads \
    && chmod -R 755 public/uploads

EXPOSE 9000

CMD ["php-fpm"] 