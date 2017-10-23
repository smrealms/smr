FROM php:5.6-apache
RUN apt-get update \
	&& apt-get install -y libcurl4-openssl-dev git sendmail \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install curl json mysqli

# We need to set 'sendmail_path' since php doesn't know about sendmail when it's built
RUN echo 'sendmail_path = "/usr/sbin/sendmail -t -i"' > /usr/local/etc/php/conf.d/mail.ini

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

# Provide a FQDN for sendmail (since /etc/hosts cannot be modified during the
# build), then start the sendmail service before initiating apache.
CMD ["sh", "-c", "echo \"$(hostname -i) $(hostname) $(hostname).localhost\" >> /etc/hosts && /usr/sbin/service sendmail restart && apache2-foreground"]
