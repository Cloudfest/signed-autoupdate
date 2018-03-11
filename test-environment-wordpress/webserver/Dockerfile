FROM php:7.2-apache
RUN docker-php-ext-install mysqli
RUN apt-get update && apt-get install -y curl wget mysql-client
ADD getcomposer.sh /root/getcomposer.sh
RUN /root/getcomposer.sh
RUN curl -o /usr/bin/wp --url https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod u+x /usr/bin/wp
