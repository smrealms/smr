FROM php:8.5.1-cli-alpine

RUN curl -L -O https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.9.1/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

ENTRYPOINT ["phpdoc"]
