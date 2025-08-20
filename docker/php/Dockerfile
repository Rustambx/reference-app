FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        vim curl debconf subversion git apt-transport-https apt-utils \
        build-essential locales acl mailutils wget zip libicu-dev libzip-dev unzip \
        gnupg gnupg1 gnupg2 libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libpq-dev \
        libfreetype6-dev \
    && docker-php-ext-install zip sockets \
    && docker-php-ext-install pgsql pdo_pgsql pdo \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls \
    && mv composer.phar /usr/local/bin/composer

RUN rm -rf /var/lib/apt/lists/*
RUN echo "dev ALL=(ALL) ALL" > /etc/sudoers

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

RUN wget https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.16.1/php-cs-fixer.phar -O php-cs-fixer
RUN chmod a+x php-cs-fixer && mv php-cs-fixer /usr/local/bin/php-cs-fixer

EXPOSE 9000
CMD ["php-fpm"]
