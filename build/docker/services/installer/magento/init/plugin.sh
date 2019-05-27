#!/bin/sh

##########################################################
## Plugin Installation
##########################################################

WEB_DIR="web"
SOURCE_DIR="source"
PLUGIN_DIR="plugin"

# When magento folder not empty
if [ "$(ls -A ${WEB_DIR})" ]; then

    echo "Processing plugins"

    IFS=" "
    set ${APP_PLUGINS}
    for plugin
    do
        case $plugin in
            *zip_payment*)

                if [ -d "${SOURCE_DIR}/${plugin}" ]; then

                    if [ ! -f "${WEB_DIR}/app/etc/modules/Zip_Payment.xml" ]; then

                        echo "Installing ${plugin} plugin"

                        if [ ! -d "${WEB_DIR}/lib/Zip" ]; then
                            mkdir "${WEB_DIR}/lib/Zip"
                        fi

                        if [ ! -d "${WEB_DIR}/app/code/community/Zip/Payment" ]; then
                            mkdir "${WEB_DIR}/app/code/community/Zip/Payment"
                        fi

                        # Copy plugin code into project
                        cp -R ${SOURCE_DIR}/${plugin}/app ${WEB_DIR}
                        cp -R ${SOURCE_DIR}/${plugin}/js ${WEB_DIR}
                    fi
                fi
            ;;
            *)
                echo "no match found in ${plugin}"
        esac

    done

fi

# update configurations
echo "Updating plugin configurations"

IFS="|"
set ${PLUGIN_CONFIGURATIONS}
for custom_config
do
    config_key=$(echo "$custom_config" | sed -e "s/=.*\$//")
    config_value=$(echo "$custom_config" | sed -e "s/\b[a-z_\/]*=//1" -e "s/\[\[:space:\]\]/ /g")

    if [ ! -z "$config_key" -a ! -z "$config_value" ]; then
        magerun --root-dir=${WEB_DIR} config:set "${config_key}" "${config_value}"
    fi

done
