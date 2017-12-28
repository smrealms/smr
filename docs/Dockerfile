FROM php:7.1 as builder

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
