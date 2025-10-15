# -------------------------------
# Stage 3: Final PHP-Apache runtime
# -------------------------------
    FROM php:8.3-apache

    # -------------------------------
    # Installer Node pour les assets frontend
    # -------------------------------
    RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
        && apt-get install -y nodejs
    
    # -------------------------------
    # Installer extensions PHP et dépendances système
    # -------------------------------
    RUN apt-get update \
        && apt-get install -y --no-install-recommends \
            libicu-dev \
            libzip-dev \
            zlib1g-dev \
            libpq-dev \
            git \
            supervisor \
            unzip \
            curl \
            ca-certificates \
        && docker-php-ext-install -j$(nproc) \
            intl \
            pdo_pgsql \
            zip \
            opcache \
        && a2enmod rewrite headers \
        && rm -rf /var/lib/apt/lists/*
    
    # -------------------------------
    # Définir le répertoire de travail
    # -------------------------------
    WORKDIR /var/www/html
    
    # -------------------------------
    # Copier le code et configs
    # -------------------------------
    COPY . .
    
    # Copier les configs supervisor
    COPY supervisor/ /etc/supervisor/conf.d/
    
    # Créer le dossier logs pour supervisor
    RUN mkdir -p /var/log/symfony
    
    
    COPY docker/php-apache/000-default.conf /etc/apache2/sites-available/000-default.conf
    COPY docker/php-apache/php.ini /usr/local/etc/php/conf.d/custom.ini
    
    # -------------------------------
    # Installer Composer et dépendances PHP
    # -------------------------------
    RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/composer \
        && composer install --no-dev \
           --prefer-dist \
           --no-progress \
           --no-interaction \
           --no-scripts
    
    # -------------------------------
    # Installer dépendances JS et compiler assets
    # -------------------------------
    RUN npm install && npm run build
    
    # -------------------------------
    # Créer un script d'entrypoint pour corriger les permissions
    # -------------------------------
    COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
    RUN chmod +x /usr/local/bin/docker-entrypoint.sh
    
    # -------------------------------
    # Healthcheck pour Docker
    # -------------------------------
    HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=5 \
        CMD curl -fsS http://localhost/ || exit 1
    
    # -------------------------------
    # Définir l'entrypoint et la commande par défaut
    # -------------------------------
    ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]