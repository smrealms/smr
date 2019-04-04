FROM php:7.3-cli-alpine

RUN docker-php-ext-install mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install SMR-related dependencies
WORKDIR /smr
COPY tools/discord/composer.json .
RUN composer install --no-interaction

# Get the SMR source code
COPY ./htdocs ./htdocs
COPY ./lib ./lib
COPY ./tools ./tools

# Set up the DiscordPHP bot
WORKDIR /smr/tools/discord

CMD ["php", "./bot.php"]
