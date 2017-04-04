BUILD=`git rev-parse --short HEAD`
echo "Building package with build number" $1;

mkdir -p var/connect && \
sed -ri 's/^(version:\s*[0-9]*\.*[0-9]*\.*[0-9]*).*/\1\.'"$1"'/gm' zipmoney.package.yaml && \
n98-magerun.phar extension:create -f zipmoney.package.yaml