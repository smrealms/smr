FROM php:7-cli
RUN apt-get update \
	&& apt-get install -y git \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install anubisbot
RUN composer create-project pazuzu156/anubisbot --prefer-dist

# Install SMR-related dependencies
WORKDIR /smr
COPY tools/discord/composer.json .
RUN composer install --no-interaction

# Get the SMR source code
COPY . .

# Set up anubisbot commands
WORKDIR /anubisbot
RUN php ./cli make:command Turns
RUN php ./cli make:command Money
RUN php ./cli make:command Game

COPY tools/discord/cli .
COPY tools/discord/registrar.php .
COPY tools/discord/commands ./app/commands

CMD ["php", "./cli", "run"]
