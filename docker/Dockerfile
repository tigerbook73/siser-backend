#
# stage: frontend
#
FROM localhost/frontend as frontend

#
# stage: final -- backend
#
FROM php:8.1-apache

# library
RUN apt update && apt install -y \
  zip \
  unzip

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# extension
RUN docker-php-source extract \
  && docker-php-ext-install bcmath \
  && docker-php-ext-install ctype \
  && docker-php-source delete \
  && ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled

# apache configuration
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# composer json and lock
COPY composer.json composer.lock ./

# install composer dependencies (without auto-generate)
RUN composer install --no-dev --no-scripts --no-autoloader

# copy backend
COPY . .

# copy frontend
COPY --from=frontend /dist/public/ ./public/
COPY --from=frontend /dist/index.html ./resources/views/index.blade.php

RUN mv ./.env.prod ./.env && \
  composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod 755 ./start.sh

CMD ["/var/www/html/start.sh"]

USER root

EXPOSE 80

# add alias
RUN echo "" >> ~/.bashrc \
  && echo "alias php='/usr/local/bin/php'" >> ~/.bashrc \
  && echo "alias art='/usr/local/bin/php /var/www/html/artisan'" >> ~/.bashrc \
  && echo "alias ll='ls -l'" >> ~/.bashrc \
  && echo "alias l='ls -la'" >> ~/.bashrc
