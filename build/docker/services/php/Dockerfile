ARG NAME
ARG VERSION

FROM $NAME:$VERSION

# install PHP extensions
RUN apt-get update && \
    apt-get install -y libxml2-dev libmcrypt4 libmcrypt-dev libpng-dev libjpeg-dev libfreetype6 libfreetype6-dev

RUN docker-php-ext-install soap pdo_mysql mcrypt
RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/lib/ --with-freetype-dir=/usr/lib/ && \
    docker-php-ext-install gd