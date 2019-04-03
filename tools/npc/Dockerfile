FROM php:7.2-cli-alpine

RUN docker-php-ext-install mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install an extension for redefining constants
# (The .build-deps package group is needed to run `pecl install`)
RUN apk add --no-cache --virtual .build-deps pcre-dev ${PHPIZE_DEPS} \
	&& pecl install uopz-6.0.1 \
	&& docker-php-ext-enable uopz \
	&& apk del .build-deps

# Install SMR-related dependencies
WORKDIR /smr
COPY tools/npc/composer.json .
RUN composer install --no-interaction

# Get the SMR source code
COPY ./engine ./engine
COPY ./htdocs ./htdocs
COPY ./lib ./lib
COPY ./tools ./tools

WORKDIR /smr/tools/npc

CMD ["php", "./npc.php"]
