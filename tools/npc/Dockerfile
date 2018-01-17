FROM php:5.6-cli
RUN apt-get update \
	&& apt-get install -y git \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# runkit is needed to use NPC's
RUN pear channel-discover zenovich.github.io/pear \
	&& pecl install zenovich/runkit-1.0.4 \
	&& docker-php-ext-enable runkit

# Install SMR-related dependencies
WORKDIR /smr
COPY tools/npc/composer.json .
RUN composer install --no-interaction

# Get the SMR source code
COPY . .

WORKDIR /smr/tools/npc

CMD ["php", "./npc.php"]
