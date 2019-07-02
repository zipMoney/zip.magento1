#!/bin/sh

##########################################################
## Project Installation
##########################################################

SOURCE_DIR="source"
TEMP_DIR="temp"
WEB_DIR="web"
DATA_SOURCE_FILE="sample.sql"

# When magento folder is empty
echo "Processing source code"

# Create temp directory
if [ ! -d "${TEMP_DIR}" ]; then
    mkdir "${TEMP_DIR}"
fi

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

                output_dir="${TEMP_DIR}/framework"

                if [ ! -d "${output_dir}" ]; then
                    mkdir "${output_dir}"
                fi

                tar -$tar_options ${SOURCE_DIR}/${source} -C ${output_dir}
                dir=$(find ${output_dir} -mindepth 1 -maxdepth 1 -type d)
                cp -R $dir ${WEB_DIR}
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

                output_dir="${TEMP_DIR}/sample"

                if [ ! -d "${output_dir}" ]; then
                    mkdir ${output_dir}
                fi

                tar -$tar_options ${SOURCE_DIR}/${source} -C ${output_dir}
                dir=$(find ${output_dir} -mindepth 1 -maxdepth 1 -type d)

                cp -R $dir/media ${WEB_DIR}/media
                cp -R $dir/skin ${WEB_DIR}/skin
                cp -R $(find $dir -name "*.sql") ${TEMP_DIR}/${DATA_SOURCE_FILE}
            fi
        ;;
        *)
            echo "no match found in ${source}"
    esac

done



# Initalize Project

echo "Initalize magento"

# Generate local xml file
echo "Generating local xml file"
magerun --root-dir="${WEB_DIR}" local-config:generate "${DATABASE_HOST}" "${DATABASE_USER}" "${DATABASE_PASSWORD}" "${DATABASE_NAME}" "files" "admin";

if [ -f "${TEMP_DIR}/${DATA_SOURCE_FILE}" ]; then
    echo "Importing sample data"
    magerun --root-dir="${WEB_DIR}" db:import ../${TEMP_DIR}/${DATA_SOURCE_FILE}
fi

# Create admin user
echo "Creating admin user"
magerun --root-dir="${WEB_DIR}" admin:user:create "${ADMIN_USERNAME}" "${ADMIN_EMAIL}" "${ADMIN_PASSWORD}" "${ADMIN_FIRSTNAME}" "${ADMIN_LASTNAME}" "Administrators"


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
