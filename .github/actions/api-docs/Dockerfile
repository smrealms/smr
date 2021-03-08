FROM php:8.0.3-cli-alpine

RUN curl -L -O https://phpdoc.org/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

ENTRYPOINT ["phpdoc"]
