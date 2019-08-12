#!/bin/sh

##########################################################
## Server Configuration
##########################################################


##########################################################
## SSL
##########################################################
if [ ! -f "${SERVER_DIR_SSL}/cacert.pem" ]; then

    # Install openssl and generate ssl certificates
    echo "Installing openssl and generate ssl certificates."
    apt-get install -y openssl
    openssl req -config ${SERVER_DIR_SSL}/csr.conf -newkey rsa:2048 -nodes -keyout ${SERVER_DIR_SSL}/key.pem -x509 -days 1825 -out ${SERVER_DIR_SSL}/cacert.pem

fi

# restart nginx
nginx -g "daemon off;"
