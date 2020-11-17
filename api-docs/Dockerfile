FROM php:7.4-cli-alpine as builder

RUN curl -L -O https://phpdoc.org/phpDocumentor.phar
RUN chmod +x phpDocumentor.phar
RUN mv phpDocumentor.phar /usr/local/bin/phpdoc

COPY . /smr
WORKDIR /smr
RUN phpdoc

#--------------------

FROM nginx:alpine

# Only the html files are needed in the nginx stage of the build
COPY --from=builder /smr/api-docs/build/ /usr/share/nginx/html/
