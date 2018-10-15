FROM php:7.2-cli-alpine as builder

# Install phpDox - https://github.com/theseer/phpdox
# - dependency: XSL
# - dependency: git (for composer and git enricher)

RUN apk upgrade --update && apk add --no-cache \
    libxslt-dev \
    git \
    && docker-php-ext-install xsl

# See https://github.com/docker-library/php/issues/240#issuecomment-305038173
RUN apk add --no-cache --repository https://dl-4.alpinelinux.org/alpine/edge/testing gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

# Install phpdox with composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require --no-plugins --no-scripts --dev theseer/phpdox
ENV PATH /root/.composer/vendor/bin:$PATH

WORKDIR "/api"

# Add the phpDox configuration file
COPY ./docs/phpdox.xml .

# Mount entire source code
# NOTE: we lose the git enricher because .git is in the .dockerignore
# (See https://github.com/moby/moby/issues/12886 to follow the issue)
COPY . ./smr/

# Run phpDox to generate the html documentation
RUN phpdox

#--------------------

FROM nginx:alpine

# Only the html files are needed in the nginx stage of the build
COPY --from=builder /api/html/ /usr/share/nginx/html/
