FROM php:8.4.3-cli-alpine AS builder

RUN curl -L -O https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.5.3/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

WORKDIR /smr
COPY src/lib src/lib
COPY phpdoc.dist.xml .
RUN phpdoc

#--------------------

FROM nginx:1.27-alpine

# Only the html files are needed in the nginx stage of the build
COPY --from=builder /smr/api-docs/build/ /usr/share/nginx/html/
