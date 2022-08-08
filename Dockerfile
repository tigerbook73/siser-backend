FROM php:8.1-apache

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# extension
RUN docker-php-source extract \
  && docker-php-ext-install bcmath \
  && docker-php-ext-install ctype \
  && docker-php-source delete \
  && ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled

