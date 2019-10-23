FROM node:alpine as builder

WORKDIR /smr/

# See https://github.com/hollandben/grunt-cache-bust/issues/236
RUN npm i --save grunt grunt-contrib-uglify grunt-contrib-cssmin grunt-cache-bust@1.4.1

# Copy the SMR source code directories
COPY admin admin
COPY engine engine
COPY htdocs htdocs
COPY lib lib
COPY templates templates

# Perform CSS/JS minification and cache busting
COPY Gruntfile.js .
RUN npx grunt

# Remove local grunt install so it is not copied to the next build stage
RUN rm -rf node_modules

#---------------------------

FROM php:7.3-apache
RUN apt-get update \
	&& apt-get install -y zip unzip \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli opcache

# Disable apache access logging (error logging is still enabled)
RUN sed -i 's|CustomLog.*|CustomLog /dev/null common|' /etc/apache2/sites-enabled/000-default.conf

# Disable apache .htaccess files (suggested optimization)
RUN sed -i 's/AllowOverride All/AllowOverride None/g' /etc/apache2/conf-enabled/docker-php.conf

WORKDIR /smr/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json .
RUN composer install --no-interaction

# Set the baseline php.ini version based on the value of PHP_DEBUG
ARG PHP_DEBUG=0
RUN MODE=$([ "$PHP_DEBUG" == "0" ] && echo "production" || echo "development") && \
	echo "Using $MODE php.ini" && \
	tar -xOvf /usr/src/php.tar.xz php-$PHP_VERSION/php.ini-$MODE > /usr/local/etc/php/php.ini

COPY --from=builder /smr .
RUN rm -rf /var/www/html/ && ln -s "$(pwd)/htdocs" /var/www/html

# Make the upload directory writable by the apache user
RUN chown www-data ./htdocs/upload

# Leverage browser caching of static assets using apache's mod_headers
COPY apache/cache-static.conf /etc/apache2/conf-enabled/cache-static.conf
RUN a2enmod headers

# Store the git commit hash of the repo in the final image
COPY .git/HEAD .git/HEAD
COPY .git/refs .git/refs
RUN cat .git/$(cat .git/HEAD | awk '{print $2}') > git-commit
