ARG PHP_VERSION=8.2

FROM php:${PHP_VERSION}-fpm

COPY dockerfiles/php/php.ini /usr/local/etc/php/conf.d/99-overrides.ini

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli gd zip intl

RUN mkdir -p /var/www/html

ARG SMF_VERSION

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

RUN set -eux; \
    SMF_VERSION_DASH="${SMF_VERSION//./-}"; \
    curl -o /tmp/smf.zip https://download.simplemachines.org/index.php/smf_${SMF_VERSION_DASH}_install.zip \
    && unzip /tmp/smf.zip -d /var/www/html \
    && rm /tmp/smf.zip

RUN set -eux; \
    SMF_VERSION_DASH="${SMF_VERSION//./-}"; \
    curl -o /tmp/spanish.zip https://download.simplemachines.org/index.php/smf_${SMF_VERSION_DASH}_language-spanish_es.zip \
    && unzip /tmp/spanish.zip -d /tmp/spanish \
    && cp -r /tmp/spanish/Themes/default/languages/*.php /var/www/html/Themes/default/languages/ \
    && rm -rf /tmp/spanish /tmp/spanish.zip

RUN set -eux; \
    SMF_VERSION_DASH="${SMF_VERSION//./-}"; \
    curl -o /tmp/russian.zip https://download.simplemachines.org/index.php/smf_${SMF_VERSION_DASH}_language-russian.zip \
    && unzip /tmp/russian.zip -d /tmp/russian \
    && cp -r /tmp/russian/Themes/default/languages/*.php /var/www/html/Themes/default/languages/ \
    && rm -rf /tmp/russian /tmp/russian.zip

RUN set -eux; \
    SMF_VERSION_DASH="${SMF_VERSION//./-}"; \
    curl -o /tmp/italian.zip https://download.simplemachines.org/index.php/smf_${SMF_VERSION_DASH}_language-italian.zip \
    && unzip /tmp/italian.zip -d /tmp/italian \
    && cp -r /tmp/italian/Themes/default/languages/*.php /var/www/html/Themes/default/languages/ \
    && rm -rf /tmp/italian /tmp/italian.zip

RUN set -eux; \
    SMF_VERSION_DASH="${SMF_VERSION//./-}"; \
    curl -o /tmp/turkish.zip https://download.simplemachines.org/index.php/smf_${SMF_VERSION_DASH}_language-turkish.zip \
    && unzip /tmp/turkish.zip -d /tmp/turkish \
    && cp -r /tmp/turkish/Themes/default/languages/*.php /var/www/html/Themes/default/languages/ \
    && rm -rf /tmp/turkish /tmp/turkish.zip

RUN curl -o /tmp/light_portal.tgz "https://custom.simplemachines.org/index.php?action=download;mod=4244;attach=294687" \
    && cp /tmp/light_portal.tgz /var/www/html/Packages/light_portal.tgz \
    && tar -xzf /tmp/light_portal.tgz -C /var/www/html --wildcards "Sources/*" "Themes/*" "portal.php" \
    && ls -l /var/www/html \
    && ls -l /var/www/html/Sources /var/www/html/Themes /var/www/html/portal.php \
    && rm /tmp/light_portal.tgz

RUN rm -f /var/www/html/install.php \
    /var/www/html/install_2-1_mysql.sql \
    /var/www/html/install_2-1_postgresql.sql \
    /var/www/html/Settings.php \
    /var/www/html/Settings_bak.php

COPY dockerfiles/Settings.php /var/www/html/Settings.php
COPY dockerfiles/Settings.php /var/www/html/Settings_bak.php

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
