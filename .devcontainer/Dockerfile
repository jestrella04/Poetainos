FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        curl \
        gnupg \
        ca-certificates \
        lsb-release \
        build-essential \
        libfreetype-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        && docker-php-ext-configure gd --with-jpeg --with-freetype \
        && docker-php-ext-install -j$(nproc) pdo_mysql gd \
        && docker-php-ext-enable pdo_mysql gd \
        && apt-get purge -y --auto-remove build-essential \
        && rm -rf /var/lib/apt/lists/*

# Install Node.js 24 (via NodeSource)
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && npm --version

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm /tmp/composer-setup.php \
    && composer --version

# Create a non-root user
RUN useradd -m -u 1000 -s /bin/bash jon

# Set working directory
WORKDIR /var/www/poetainos