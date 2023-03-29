FROM php:8.1-fpm-alpine as base

# Install base packages
RUN apk update
RUN apk upgrade

RUN apk add --no-cache bash curl wget vim mysql-client msmtp procps shadow libzip libpng libjpeg-turbo libwebp freetype icu

RUN apk add --no-cache --virtual build-essentials \
    icu-libs curl-dev zlib-dev make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev

# Add extension
RUN docker-php-ext-configure gd \
        --enable-gd  \
		--with-freetype \
		--with-jpeg \
		--with-webp

# Add and Enable PHP Extenstions
RUN docker-php-ext-install  \
		bcmath \
		exif \
		gd \
        curl \
        fileinfo \
		mysqli \
		zip \
        pdo_mysql \
        opcache

RUN apk del build-essentials && rm -rf /usr/src/php*

# Install Composer
RUN wget https://getcomposer.org/composer-stable.phar -O /usr/local/bin/composer && chmod +x /usr/local/bin/composer

# Remove Cache
RUN rm -rf /var/cache/apk/*

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

RUN mkdir -p /code
RUN chown -R www:www /code

FROM base AS local
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
USER www
WORKDIR /code
EXPOSE 9000
CMD ["php-fpm"]

FROM base AS development
# install xdebug
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug \
    && apk del .phpize-deps
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
USER www
WORKDIR /code
EXPOSE 9000-9003
CMD ["php-fpm"]

FROM base AS staging
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY --chown=www:www . /code
USER www
WORKDIR /code
RUN composer install --no-dev
EXPOSE 9000
VOLUME [ "/code" ]
CMD ["php-fpm"]
