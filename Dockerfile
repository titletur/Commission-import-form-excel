# ใช้ PHP 8.2
FROM php:8.2-fpm

# ติดตั้ง dependencies ที่จำเป็น
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# ติดตั้ง PHP extensions ที่จำเป็น
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ตั้งค่า work directory
WORKDIR /var/www

# คัดลอกโปรเจกต์ทั้งหมดลงใน container
COPY . .

# ติดตั้ง dependencies ของ Laravel
RUN composer install --no-dev --optimize-autoloader

# ตั้งค่า permission สำหรับ storage และ bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# เปิดพอร์ต 9000 สำหรับ PHP-FPM
EXPOSE 9000

# คำสั่งเริ่มต้นของ container
CMD ["php-fpm"]
