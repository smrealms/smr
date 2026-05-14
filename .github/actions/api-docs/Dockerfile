FROM php:8.5.6-cli-alpine

RUN curl -L -O https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.10.0/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

ENTRYPOINT ["phpdoc"]
