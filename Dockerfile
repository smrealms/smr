FROM php:5.6-apache
RUN apt-get update \
	&& apt-get install -y libcurl4-openssl-dev git \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install curl json mysql

WORKDIR /usr/share/smr/

# runkit is needed to use NPC's
RUN pear channel-discover zenovich.github.io/pear \
	&& pecl install zenovich/runkit-1.0.4 \
	&& docker-php-ext-enable runkit

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json .
RUN composer install --no-interaction

COPY . .
VOLUME htdocs/upload
RUN rm -rf /var/www/html/ && ln -s "$(pwd)/htdocs" /var/www/html
