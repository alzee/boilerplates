#!/bin/bash
#
# vim:ft=sh

set -e

project=$(basename $PWD)
passwd=111

[ -f .env.local ] && . .env.local

sudo -u postgres psql -c "create role $project with login createdb password '$project'";

sudo chown -R al:nginx var/
sudo chown -R al:nginx public/
find public/ -type d -exec chmod 775 {} \;
find var/ -type d -exec chmod 775 {} \;
find var/ -type f -exec chmod 664 {} \;

bin/console doc:data:create
bin/console doc:m:m -n

bin/console adduser --root root $passwd
bin/console adduser -s al $passwd
bin/console adduser -a admin $passwd

#bin/console lexik:jwt:generate-keypair --overwrite -n

if [ "$APP_ENV" = prod ]; then
    bin/console asset-map:compile
    bin/console secrets:generate-keys
    # bin/console secrets:set APP_SECRET
fi

