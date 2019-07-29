#!/bin/bash

# Based on https://gist.github.com/pkuczynski/8665367

function parse_yaml() {
    local yaml_file=$1
    local prefix=${2:-""}
    local s
    local w
    local fs

    s='[[:space:]]*'
    w='[a-zA-Z0-9_.-]*'
    any='.*'
    fs="$(echo @|tr @ '\034')"

    sed -e "/- [^\"][^\'].*:[[:space:]]/s|\([ ]*\)- \($s\)|\1-\n  \1\2|g" "$yaml_file" |

    sed -ne '/^--/s|--||g; s|\"|\\\"|g; s/\\s*$//g;' \
        -e "/#.*[\"\']/!s| #.*||g; /^#/s|#.*||g;" \
        -e "s|^\($s\)\($w\)$s:$s\"\($any\)\"$s\$|\1$fs\2$fs\3|p" \
        -e "s|^\($s\)\($w\)$s[:-]$s\($any\)$s\$|\1$fs\2$fs\3|p" |

    awk -F "$fs" '{
            indent = length($1)/4;

            if (length($2) == 0) { 
                conj[indent]="+";
                sep[indent]="|";
            } else {
                conj[indent]="";
                sep[indent]="";
            }

            vname[indent]=$2;

            for (i in vname) {
                if (i > indent) {
                    delete vname[i];
                }
            }

            if (length($3) > 0) {
                vn=""; 
                for (i=0; i<indent; i++) {
                    vn=(vn)(vname[i])("_")
                }
                gsub(/[[:space:]]/, "[[:space:]]", $3)
                printf("%s%s%s%s=%s%s\n", "'"$prefix"'", toupper(vn), toupper($2), conj[indent-1], $3, sep[indent]);
            }
        }' |
        
    sed -e 's/_=/+=/g' |
    
    awk 'BEGIN {
             FS="=";
             OFS="="
         }
         /(-|\.).*=/ {
             gsub("-|\\.", "_", $1)
         }
         { print }'
}