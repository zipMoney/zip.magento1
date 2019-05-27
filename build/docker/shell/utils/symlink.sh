#!/bin/bash

##########################################################
## Symlink
##########################################################

machine="$(machine)"

# Cross-platform symlink function. With one parameter, it will check
# whether the parameter is a symlink. With two parameters, it will create
# a symlink to a file or directory, with syntax: link $linkname $target
createSymlink() {
    if [[ -z "$2" ]]; then
        # Link-checking mode.
        if [[ "$machine" == "Windows" ]]; then
            fsutil reparsepoint query "$1" > /dev/null
        else
            [[ -h "$1" ]]
        fi
    else
        # Link-creation mode.
        if [[ "$machine" == "Windows" ]]; then
            # Windows needs to be told if it's a directory or not. Infer that.
            # Also: note that we convert `/` to `\`. In this case it's necessary.
            if [[ -d "$2" ]]; then
                cmd <<< "mklink /D \"$1\" \"${2//\//\\}\"" > /dev/null
            else
                cmd <<< "mklink \"$1\" \"${2//\//\\}\"" > /dev/null
            fi
        else
            # You know what? I think ln's parameters are backwards.
            ln -s "$2" "$1"
        fi
    fi
}

# Remove a link, cross-platform.
removeSymlink() {
    if [[ "$machine" == "Windows" ]]; then
        # Again, Windows needs to be told if it's a file or directory.
        if [[ -d "$1" ]]; then
            rmdir "$1";
        else
            rm "$1"
        fi
    else
        rm "$1"
    fi
}