FROM php:7

# Install phpDox - https://github.com/theseer/phpdox
# - dependency: XSL
# - dependency: git (for composer and git enricher)

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y \
    libxslt-dev \
    git \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install xsl

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require --no-plugins --no-scripts --dev theseer/phpdox
ENV PATH /root/.composer/vendor/bin:$PATH

WORKDIR "/api"
ENTRYPOINT ["phpdox"]
