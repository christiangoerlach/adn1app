FROM php:8.2-fpm

# Installiere System-Abhängigkeiten
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Installiere Microsoft SQL Server Treiber
# Installiere ODBC-Treiber und SQL Server Extensions
RUN apt-get update && apt-get install -y \
    unixodbc-dev \
    apt-transport-https \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Installiere PHP Extensions
RUN docker-php-ext-install pdo_mysql pdo mbstring exif pcntl bcmath gd

# Installiere Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setze Arbeitsverzeichnis
WORKDIR /var/www/html

# Composer wird über Volume-Mount verfügbar sein
# Dependencies können lokal installiert werden oder im Container

# Setze Berechtigungen
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# PHP-Konfiguration (Azure-ähnlich)
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/docker-php-memory.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/docker-php-timeout.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/docker-php-upload.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/docker-php-upload.ini \
    && echo "date.timezone = Europe/Berlin" >> /usr/local/etc/php/conf.d/docker-php-timezone.ini

# Exponiere Port 9000 für PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]

