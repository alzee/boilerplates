#!/bin/bash
#
# vim:ft=sh

############### Variables ###############

############### Functions ###############

############### Main Part ###############
project=$(basename $PWD)
passwd=111

[ -f .env.local ] && . .env.local

sudo -u postgres psql -c "create role $project with login createdb password '$project'";

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

