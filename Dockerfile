FROM php:8.2-fpm

# Install system dependencies and clean up in one step
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PCNTL extension separately first
RUN docker-php-ext-install pcntl

# Then install other PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    bcmath \
    gd

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy only necessary files first
COPY composer.* ./
COPY package*.json ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader
RUN npm install

# Copy the rest of the application
COPY . .

# Final steps
RUN composer dump-autoload --optimize \
    && npm run build \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy the start script
COPY start.sh /usr/local/bin/start.sh

# Make sure the script is executable
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8000

CMD ["/usr/local/bin/start.sh"]