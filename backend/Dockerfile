FROM php:8.1.16-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libgmp-dev \
    libzip-dev \
    libsodium-dev \
    mariadb-client \
    git \
    zip \
    unzip \
    curl

# Install extensions
RUN apt-get update \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip gd pdo_mysql bcmath gmp sodium

# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath

# Set working directory
WORKDIR /var/www/html/backend

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . .

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html
RUN chown -R www:www  /var/www/html
# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
