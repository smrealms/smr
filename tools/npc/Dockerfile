FROM php:7.2-cli
RUN apt-get update \
	&& apt-get install -y git \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install an extension for redefining constants
RUN pecl install uopz-5.0.2 \
	&& docker-php-ext-enable uopz

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
