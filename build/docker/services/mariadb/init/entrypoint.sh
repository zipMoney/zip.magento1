#!/bin/sh

##########################################################
## Sample Data
##########################################################

 if [ -n "${APP_DATA_FILE}" ]; then

    if [ -f "${APP_DATA_FILE}" ]; then
        # decompress sql file and import into database
        zcat ${APP_DATA_FILE} | mysql --user=root --password=${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE};
    fi

fi
