FROM node:alpine as builder

WORKDIR /smr/

RUN npm i --save grunt grunt-contrib-uglify grunt-contrib-cssmin grunt-cache-bust@1.7.0

# Copy the SMR source code directories
COPY src src

# Perform CSS/JS minification and cache busting
COPY Gruntfile.js .
RUN npx grunt

# Remove local grunt install so it is not copied to the next build stage
RUN rm -rf node_modules

#---------------------------

FROM php:8.0.11-apache
RUN apt-get --quiet=2 update \
	&& apt-get --quiet=2 install zip unzip \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli opcache > /dev/null

# Set the baseline php.ini version based on the value of PHP_DEBUG
ARG PHP_DEBUG=0
RUN MODE=$([ "$PHP_DEBUG" = "1" ] && echo "development" || echo "production") \
	&& echo "Using $MODE php.ini" \
	&& mv "$PHP_INI_DIR/php.ini-$MODE" "$PHP_INI_DIR/php.ini"

# Install xdebug (use /tmp/xdebug for profiler output)
RUN if [ "$PHP_DEBUG" = "1" ]; \
	then \
		pecl install xdebug-3.0.4 > /dev/null \
		&& docker-php-ext-enable xdebug \
		&& echo "xdebug.output_dir = /tmp/xdebug" > "$PHP_INI_DIR/conf.d/xdebug.ini" \
		&& mkdir /tmp/xdebug; \
	fi

# Disable apache access logging (error logging is still enabled)
RUN sed -i 's|CustomLog.*|CustomLog /dev/null common|' /etc/apache2/sites-enabled/000-default.conf

# Disable apache .htaccess files (suggested optimization)
RUN sed -i 's/AllowOverride All/AllowOverride None/g' /etc/apache2/conf-enabled/docker-php.conf

WORKDIR /smr/

RUN curl -sS https://getcomposer.org/installer | \
	php -- --install-dir=/usr/local/bin --filename=composer --version=2.1.6

COPY composer.json .
RUN MODE=$([ "$PHP_DEBUG" = "1" ] || echo "--no-dev") \
	&& composer update $MODE --quiet --no-interaction

COPY --from=builder /smr .
RUN rm -rf /var/www/html/ && ln -s "$(pwd)/src/htdocs" /var/www/html

# Make the upload directory writable by the apache user
RUN chown www-data ./src/htdocs/upload

# Leverage browser caching of static assets using apache's mod_headers
COPY apache/cache-static.conf /etc/apache2/conf-enabled/cache-static.conf
RUN a2enmod headers

# Store the git commit hash of the repo in the final image
COPY .git/HEAD .git/HEAD
COPY .git/refs .git/refs
RUN REF="ref: HEAD" \
	&& while [ -n "$(echo $REF | grep ref:)" ]; do REF=$(cat ".git/$(echo $REF | awk '{print $2}')"); done \
	&& echo $REF > git-commit
