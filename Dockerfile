# src: https://github.com/qlico/project-examples/blob/main/Dockerfile.php80-minimal
FROM harbor.webstores.nl/docker-hub-cache/library/php:8.0.5-fpm-alpine3.13 as base
LABEL maintainer="Qlico <hello@qlico.dev>"

ARG LOCAL_USER_ID=1000
ARG LOCAL_GROUP_ID=1000

# persistent / runtime depsfmusl
ENV PHPIZE_DEPS \
    autoconf \
    cmake \
    file \
    g++ \
    gcc \
    gettext-dev \
    git \
    icu-dev \
    libc-dev \
    libxml2-dev \
    libzip-dev \
    make \
    pcre-dev \
    pkgconf \
    postgresql-dev \
    re2c

RUN apk add --no-cache --virtual .persistent-deps \
    gettext \
    git \
    gnu-libiconv \
    icu \
    libintl \
    libpq \
    libzip \
    && apk add --no-cache --virtual .build-deps \
       $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-install -j "$(nproc)" \
       bcmath \
       exif \
       gettext \
       intl \
       opcache \
       pcntl \
       mysqli \
       pdo_mysql \
       pdo_pgsql \
       shmop \
       soap \
       sockets \
       sysvmsg \
       sysvsem \
       sysvshm \
       zip \
    && pecl install \
       APCu \
       ds \
       redis \
    && docker-php-ext-enable \
       apcu \
       ds \
       redis \
    && apk del .build-deps \
    && docker-php-source delete \
    && apk --no-cache -U upgrade \
    && rm -rf /tmp/* \
    && addgroup -g $LOCAL_GROUP_ID -S qlico \
    && adduser -u $LOCAL_USER_ID -S qlico -G qlico

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer.phar

# Necessary for iconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

# Add composer and php scripts for aliases.
COPY qlico/services/php/scripts /usr/local/sbin
RUN chmod +x /usr/local/sbin/composer \
             /usr/local/sbin/php

# Disabled access logs for php-fpm
RUN sed -i 's/access.log = \/proc\/self\/fd\/2/access.log = \/proc\/self\/fd\/1/g' /usr/local/etc/php-fpm.d/docker.conf

# php.ini
COPY qlico/services/php/prod/php.ini $PHP_INI_DIR

# www.cnf
COPY qlico/services/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# @TODO: Dit moet op een later moment weer worden verwijdert!
COPY qlico/services/php/prod/zzz-instana-extras.ini /usr/local/etc/php/conf.d/zzz-instana-extras.ini

# Don't run as the default (root) user.
USER qlico

CMD ["php-fpm"]

FROM base as dev
USER root

RUN apk add --no-cache --virtual . \
    # Local mail handling
    msmtp

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
       $PHPIZE_DEPS \
    && pecl install \
       xdebug \
    && docker-php-ext-enable \
       xdebug \
    && apk del .build-deps \
    && docker-php-source delete \
    # Install Blackfire
    && version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && architecture=$(case $(uname -m) in i386 | i686 | x86) echo "i386" ;; x86_64 | amd64) echo "amd64" ;; aarch64 | arm64 | armv8) echo "arm64" ;; *) echo "amd64" ;; esac) \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/$architecture/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/*

# MSMTP config.
COPY qlico/services/php/dev/msmtprc /etc/msmtprc

# Xdebug config.
COPY qlico/services/php/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# php.ini
COPY qlico/services/php/dev/php.ini /usr/local/etc/php

# Don't run as the default (root) user.
USER qlico

CMD ["php-fpm"]

FROM base as prod
USER root

RUN set -xe \
    # Remove packages we don't want in production
    && rm -rf /usr/local/sbin/composer \
    && rm -rf /usr/local/bin/composer.phar \
    && apk del git \
    && rm -rf /usr/bin/git \
    # create app folder
    && mkdir /app \
    && chown qlico: /app

USER qlico

# Copy the application to the Docker image.
COPY . /app

USER root

# Remove qlico folder.
RUN rm -rf /app/qlico

# Don't run as the default (root) user.
USER qlico

CMD ["php-fpm"]
