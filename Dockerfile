# Image de base PHP avec Apache
FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activation du module Apache rewrite
RUN a2enmod rewrite

# Copie du code de l'application
COPY . /var/www/html/

# Configuration des permissions
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Configuration du serveur Apache
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Port exposé
EXPOSE 80

# Démarrage d'Apache
CMD ["apache2-foreground"]
