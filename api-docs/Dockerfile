FROM php:8.4.6-cli-alpine AS builder

RUN curl -L -O https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.7.1/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

WORKDIR /smr
COPY src/lib src/lib
COPY phpdoc.dist.xml .
RUN phpdoc

#--------------------

FROM nginx:1.27.5-alpine

# Only the html files are needed in the nginx stage of the build
COPY --from=builder /smr/api-docs/build/ /usr/share/nginx/html/
