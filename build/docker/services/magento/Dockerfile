ARG NAME
ARG VERSION

FROM $NAME:$VERSION

WORKDIR /tmp

# install packages
RUN apt-get update && \
    apt-get install -y mysql-client
RUN docker-php-ext-install mysql && docker-php-ext-enable mysql
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable pdo_mysql

# Install php composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

# Download and install magerun
RUN curl https://files.magerun.net/n98-magerun.phar -o n98-magerun.phar
RUN chmod +x n98-magerun.phar
RUN cp n98-magerun.phar /usr/local/bin/magerun
