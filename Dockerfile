FROM node:alpine as builder

WORKDIR /smr/

# See https://github.com/hollandben/grunt-cache-bust/issues/236
RUN npm i --save grunt grunt-contrib-uglify grunt-contrib-cssmin grunt-cache-bust@1.4.1

# Copy the SMR source code
COPY . .

# Perform CSS/JS minification and cache busting
RUN npx grunt

# Remove local grunt install so it is not copied to the next build stage
RUN rm -rf node_modules

#---------------------------

FROM php:7.2-apache
RUN apt-get update \
	&& apt-get install -y zip unzip sendmail \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli

# Use the production php.ini unless PHP_DEBUG=1 (defaults to 0)
ARG PHP_DEBUG=0
RUN [ "$PHP_DEBUG" = "1" ] && echo "Using development php.ini" || \
	{ echo "Using production php.ini" \
		&& mkdir /usr/src/php \
		&& tar --file /usr/src/php.tar.xz --extract --strip-components=1 --directory /usr/src/php \
		&& cp /usr/src/php/php.ini-production /usr/local/etc/php/php.ini; \
	}

# We need to set 'sendmail_path' since php doesn't know about sendmail when it's built
RUN echo 'sendmail_path = "/usr/sbin/sendmail -t -i"' > /usr/local/etc/php/conf.d/mail.ini

# Disable apache .htaccess files (suggested optimization)
RUN sed -i 's/AllowOverride All/AllowOverride None/g' /etc/apache2/conf-enabled/docker-php.conf

WORKDIR /smr/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json .
RUN composer install --no-interaction

COPY --from=builder /smr .
RUN rm -rf /var/www/html/ && ln -s "$(pwd)/htdocs" /var/www/html

# Make the upload directory writable by the apache user
RUN chown www-data ./htdocs/upload

# Provide a FQDN for sendmail (since /etc/hosts cannot be modified during the
# build), then start the sendmail service before initiating apache.
CMD ["sh", "-c", "echo \"$(hostname -i) $(hostname) $(hostname).localhost\" >> /etc/hosts && /usr/sbin/service sendmail restart && apache2-foreground"]
