# ============================================
# DOCKERFILE - PHP 8.2 + Apache
# ============================================
# Imazhi bazë me PHP dhe Apache të integruar

FROM php:8.2-apache

# Metadata
LABEL maintainer="WebDev Team"
LABEL description="PHP 8.2 with Apache for WebDev E-commerce"

# ============================================
# INSTALIMI I EXTENSIONS PHP
# ============================================

# Instalon dependencat e sistemit
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalon extensions PHP të nevojshme
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# ============================================
# KONFIGURIMI I APACHE
# ============================================

# Aktivizon mod_rewrite për URL të bukura
RUN a2enmod rewrite headers

# Konfiguron DocumentRoot në rrënjën e projektit (jo public/)
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Lejon aksesin në të gjithë dosjen
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/webdev.conf \
    && a2enconf webdev

# Lejon .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# ============================================
# KONFIGURIMI I DOSJES SË PUNËS
# ============================================

# Vendos dosjen e punës
WORKDIR /var/www/html

# Kopjo skedarët e projektit
COPY . /var/www/html

# Krijo dosjet e nevojshme
RUN mkdir -p /var/www/html/uploads/products \
    && mkdir -p /var/www/html/uploads/users \
    && mkdir -p /var/www/html/logs

# Vendos lejet e duhura
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs

# ============================================
# PORTA DHE KOMANDA
# ============================================

# Ekspozon portën 80
EXPOSE 80

# Komanda për të nisur Apache
CMD ["apache2-foreground"]
