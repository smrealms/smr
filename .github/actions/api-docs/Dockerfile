FROM php:8.1.3-cli-alpine

RUN curl -L -O https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.3.0/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

ENTRYPOINT ["phpdoc"]
