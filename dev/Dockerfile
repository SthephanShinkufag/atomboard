FROM php:8.3-apache AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        libavif-dev libfreetype6-dev libjpeg62-turbo-dev libonig-dev \
        libpng-dev libtidy-dev libwebp-dev mediainfo ffmpegthumbnailer \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-avif --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd mbstring mysqli pdo_mysql tidy \
    && a2enmod rewrite headers expires \
    && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data . /var/www/html/test/
COPY dev/index.php /var/www/html/index.php
COPY dev/entrypoint.sh /usr/local/bin/atomboard-entrypoint
RUN chmod +x /usr/local/bin/atomboard-entrypoint \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html/test
ENTRYPOINT ["atomboard-entrypoint"]
CMD ["apache2-foreground"]

FROM app AS http-test
RUN php dev/inject-captcha.php \
    && php dev/inject-embed.php \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && printf 'pcov.enabled=1\npcov.directory=/var/www/html/test\nauto_prepend_file=/var/www/html/test/dev/coverage-prepend.php\nupload_max_filesize=64M\npost_max_size=80M\n' \
        > /usr/local/etc/php/conf.d/http-test.ini \
    && rm -rf /var/lib/apt/lists/*

FROM app AS production
