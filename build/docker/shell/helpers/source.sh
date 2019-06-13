#!/bin/bash

##########################################################
## Get code sources
##########################################################



# Update source env variables
SOURCE_TYPES=( "FRAMEWORK" "SAMPLE" "PLUGINS" )

sources=()
plugins=()
plugin_config=()

url_regex="(https?|ftp|file)://[-A-Za-z0-9\+&@#/%?=~_|!:,.;]*[-A-Za-z0-9\+&@#/%=~_|]"
git_regex="(https?://|git@)[-A-Za-z0-9\+&@#/%?=~_|!:,.;]*[-A-Za-z0-9\+&@#/%=~_|].git$"

for SOURCE_TYPE in "${SOURCE_TYPES[@]}"
do
    case $SOURCE_TYPE in
        FRAMEWORK|SAMPLE*)

            version_var="APP_${SOURCE_TYPE}_VERSION"
            source_var="APP_${SOURCE_TYPE}_SOURCE"

            if [[ ! -z "${!version_var}" ]]; then

                source_location=${!source_var}

                # source url has been defined
                if [[ ! -z "${!source_var}" ]]; then

                    version_substitude="\\$\{VERSION\}"
                    source_url=$(printf ${source_location} | sed -E "s/${version_substitude}/${!version_var}/g")
                    extension=$(getFileExtension $source_url)

                    source_file=${APP_FRAMEWORK}-$(echo "${SOURCE_TYPE}" | tr '[:upper:]' '[:lower:]')-${!version_var}.${extension}
                    source_path=./build/docker/${SOURCE_DIR}/${source_file}

                    sources+=("${source_file} ")

                    if [ ! -f "${source_path}" ]; then
                        echo "Download source code from ${source_url} into ${source_path}"
                        curl -LJ ${source_url} -o ${source_path} || exit 1
                    fi
                else
                    # source url has not been identified
                    sources+=("$(echo "${SOURCE_TYPE}" | tr '[:upper:]' '[:lower:]')-${source_location} ")
                fi
            fi
        ;;
        PLUGINS*)

            for plugin_version_var in $(compgen -A variable | grep "^APP_PLUGIN_[A-Z_]*_VERSION$")
            do

                if [[ $plugin_version_var =~ ^APP_PLUGIN_([A-Z_]*)_VERSION$ ]]; then

                    plugin_name=${BASH_REMATCH[1]}
                    version_var="APP_PLUGIN_${plugin_name}_VERSION"
                    source_var="APP_PLUGIN_${plugin_name}_SOURCE"
                    config_var="APP_PLUGIN_${plugin_name}_CONFIG"

                    # source is defined
                    if [[ ! -z "${!source_var}" ]]; then

                        plugin_name=${BASH_REMATCH[1]}
                        version_substitude="\\$\{VERSION\}"
                        source_location=${!source_var}

                        # if source is a git repo
                        if [[ ${source_location} =~ ${git_regex} ]]; then

                            source_path=${SOURCE_DIR}/$(echo "${plugin_name}" | tr '[:upper:]' '[:lower:]')

                            if [ ! -d "${source_path}" ]; then
                                git clone ${source_location} ${source_path}
                            fi

                            plugins+=("$(echo "${plugin_name}" | tr '[:upper:]' '[:lower:]') ")

                            # if source is a download url
                            elif [[ ${source_location} =~ ${url_regex} ]]; then

                            source_url=$(printf ${source_location} | sed -E "s/${version_substitude}/${!version_var}/g")
                            extension=$(getFileExtension $source_url)
                            source_file=${APP_FRAMEWORK}-plugin-$(echo "${plugin_name}" | tr '[:upper:]' '[:lower:]')-${!version_var}.${extension}
                            source_path=./build/docker/${SOURCE_DIR}/${source_file}

                            if [ ! -f "${source_path}" ]; then
                                echo "Download source code from ${source_url} into ${source_path}"
                                curl -LJ ${source_url} -o ${source_path} || exit 1
                            fi

                            plugins+=("${source_file} ")
                        fi
                        # does not include any source for the plugin
                    else
                        plugins+=("$(echo "${plugin_name}" | tr '[:upper:]' '[:lower:]')-${!version_var} ")
                    fi

                    if [ ! -z ${!config_var} ]; then
                        plugin_config+=("${!config_var}")
                    fi

                fi

            done
        ;;
        *)
            log ERROR "Sorry, we can't recoginize this source"
        ;;
    esac

done

export APP_SOURCES=${sources[*]}
export APP_PLUGINS=${plugins[*]}
export PLUGIN_CONFIGURATIONS=$(echo ${plugin_config[*]} | sed -E "s/|[[:space:]]{1}//g")
