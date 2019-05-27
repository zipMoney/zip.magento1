#!/bin/sh

##########################################################
## Project Installation
##########################################################

WEB_DIR="web"
SOURCE_DIR="source"
DATA_SOURCE_FILE="sample.sql"

# When magento folder is empty
if [ ! "$(ls -A ${WEB_DIR})" ]; then

    echo "Processing source code"

    IFS=" "
    set ${APP_SOURCES}
    for source
    do
        case $source in
            *framework*)
                if [ -f "${SOURCE_DIR}/${source}" ]; then
                    extension=${source##*.}
                    tar_options="xvf"

                    if [ $extension == "gz" ]; then
                        tar_options="xvzf"
                        elif [ $extension == "bz2" ]; then
                        tar_options="xvjf"
                    fi

                    tar -$tar_options ${SOURCE_DIR}/${source} -C ${WEB_DIR}
                    dir=$(find ${WEB_DIR} -mindepth 1 -maxdepth 1 -type d)
                    mv $dir/* $dir/.* ${WEB_DIR}
                    rm -rf $dir
                fi
            ;;
            *sample*)
                if [ -f "${SOURCE_DIR}/${source}" ]; then
                    extension=${source##*.}
                    tar_options="xvf"

                    if [ $extension == "gz" ]; then
                        tar_options="xvzf"
                        elif [ $extension == "bz2" ]; then
                        tar_options="xvjf"
                    fi

                    sample_dir="${SOURCE_DIR}/sample"

                    if [ ! -d "${sample_dir}" ]; then
                        mkdir ${sample_dir}
                    fi

                    tar -$tar_options ${SOURCE_DIR}/${source} -C ${sample_dir}
                    dir=$(find ${sample_dir} -mindepth 1 -maxdepth 1 -type d)

                    mv $dir/media/* $dir/media/.* ${WEB_DIR}/media
                    mv $dir/skin/* $dir/skin/.* ${WEB_DIR}/skin
                    mv $(find $dir -name "*.sql") ${WEB_DIR}/${DATA_SOURCE_FILE}

                    rm -rf ${sample_dir}
                fi
            ;;
            *)
                echo "no match found in ${source}"
        esac

    done

fi


# Initalize Project
if [ ! -f "web/app/etc/local.xml" ]; then

    echo "Initalize magento"

    # Generate local xml file
    echo "Generating local xml file"
    magerun --root-dir="${WEB_DIR}" local-config:generate "${DATABASE_HOST}" "${DATABASE_USER}" "${DATABASE_PASSWORD}" "${DATABASE_NAME}" "files" "admin";

    if [ -f "${WEB_DIR}/${DATA_SOURCE_FILE}" ]; then
        echo "Importing sample data"
        magerun --root-dir="${WEB_DIR}" db:import ${DATA_SOURCE_FILE}
    fi

    # Create admin user
    echo "Creating admin user"
    magerun --root-dir="${WEB_DIR}" admin:user:create "${ADMIN_USERNAME}" "${ADMIN_EMAIL}" "${ADMIN_PASSWORD}" "${ADMIN_FIRSTNAME}" "${ADMIN_LASTNAME}" "Administrators"

fi

# Update Configurations
echo "Updating Configurations"
magerun --root-dir="${WEB_DIR}" config:set "web/unsecure/base_url" "http://${APP_HOST}/"
magerun --root-dir="${WEB_DIR}" config:set "web/secure/base_url" "https://${APP_HOST}/"

# update custom configurations
IFS="|"
set ${APP_CONFIGURATIONS}
for custom_config
do
    config_key=$(echo "$custom_config" | sed -e "s/=.*\$//")
    config_value=$(echo "$custom_config" | sed -e "s/\b[a-z_\/]*=//1" -e "s/\[\[:space:\]\]/ /g")

    if [ ! -z "$config_key" -a ! -z "$config_value" ]; then
        magerun --root-dir=${WEB_DIR} config:set "${config_key}" "${config_value}"
    fi
done
