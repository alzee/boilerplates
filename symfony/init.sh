#!/bin/bash
#
# vim:ft=sh

############### Variables ###############

############### Functions ###############

############### Main Part ###############
project=$(basename $PWD)

sudo -u postgres psql -c "create role $project with login createdb password '$project'";

bin/console doc:data:create
bin/console doc:m:m -n

bin/console adduser --root root 111
bin/console adduser -s al 111
bin/console adduser -a admin 111

#bin/console lexik:jwt:generate-keypair --overwrite -n

bin/console asset-map:compile

