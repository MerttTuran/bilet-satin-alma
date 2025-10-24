# Dockerfile - PHP ile Otobüs Bilet Satış Platformu

FROM php:8.2-apache

# Sistem paketlerini güncelle ve gerekli araçları yükle
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# Uygulama dosyalarını kopyala
COPY . /var/www/html/

# Gerekli dizinleri oluştur ve izinleri ayarla
RUN mkdir -p /var/www/html/database \
    /var/www/html/uploads/logos \
    /var/www/html/temp \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/database \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/temp

# Apache yapılandırması
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/custom.conf \
    && a2enconf custom

# .htaccess dosyası oluştur
RUN echo 'RewriteEngine On\n\
RewriteCond %{REQUEST_FILENAME} !-f\n\
RewriteCond %{REQUEST_FILENAME} !-d\n\
RewriteRule ^(.*)$ index.php [L,QSA]' > /var/www/html/.htaccess

# Port 80'i aç
EXPOSE 80

# Başlangıç scripti
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

