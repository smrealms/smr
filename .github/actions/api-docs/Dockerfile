FROM php:7.4-cli-alpine

RUN curl -L -O https://phpdoc.org/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

ENTRYPOINT ["phpdoc"]
