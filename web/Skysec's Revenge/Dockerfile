FROM php:7.2-apache

COPY *.php /var/www/html/

COPY flag.txt /flag.txt

RUN mkdir -p /var/www/html/uploads
RUN chown -R www-data:www-data /var/www/html/uploads

RUN mkdir -p /var/www/html/.git/logs/refs/heads \
    && mkdir -p /var/www/html/.git/refs/heads \
    && mkdir -p /var/www/html/.git/objects

COPY git-files/config /var/www/html/.git/config
COPY git-files/HEAD /var/www/html/.git/HEAD
COPY git-files/description /var/www/html/.git/description
COPY git-files/COMMIT_EDITMSG /var/www/html/.git/COMMIT_EDITMSG
COPY git-files/logs/HEAD /var/www/html/.git/logs/HEAD
COPY git-files/refs/heads/main /var/www/html/.git/refs/heads/main

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini

EXPOSE 80

CMD ["apache2-foreground"]
