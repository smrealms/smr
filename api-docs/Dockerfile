FROM php:8.0.8-cli-alpine as builder

RUN curl -L -O https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.1.0/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

WORKDIR /smr
COPY src/lib src/lib
COPY phpdoc.dist.xml .
RUN phpdoc

#--------------------

FROM nginx:1.21-alpine

# Only the html files are needed in the nginx stage of the build
COPY --from=builder /smr/api-docs/build/ /usr/share/nginx/html/
