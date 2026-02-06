#!/bin/bash
#
# vim:ft=sh
# 5 5 * * * /home/al/w/dbname/cron.sh

############### Variables ###############

############### Functions ###############

############### Main Part ###############

pdir=$(dirname $0)
. $pdir/.env.local

# DATABASE_URL="mysql://user:pass@127.0.0.1:3306/dbname?serverVersion=mariadb-10.5.18&charset=utf8mb4"
# DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/dbname?serverVersion=16&charset=utf8"
# echo $DATABASE_URL
t=${DATABASE_URL#*//} # user:pass@127.0.0.1:3306/dbname?serverVersion=mariadb-10.5.18&charset=utf8mb4
user=${t%%:*}
# echo $user
tt=${t%%@*} # user:pass
passwd=${tt##*\:}
# echo $passwd
tt=${t%%\?*} # user:pass@127.0.0.1:3306/dbname
db=${tt##*/}
# echo $db
tt=${t#*@} # 127.0.0.1:3306/dbname
host=${tt%\:*}
# echo $host
tt=${tt#*\:} # 3306/dbname
port=${tt%/*}
# echo $port

# echo dump db...
# echo User: $user
# echo DB: $db
# echo host: $host
# echo port: $port

dir=~/w/db.$db
mkdir -p $dir; cd $dir

if git status &> /dev/null; then
    # mysqldump --skip-extended-insert -u$user -p$passwd -h $host -P $port $db  > db.sql
    pg_dump -U $user -w -h $host -p $port $db > db.sql
    echo -- $(date) >> db.sql
    git add .
    git commit -m "db dump" --no-gpg-sign > /dev/null
    git push origin main &> /dev/null
else
    [ "$?" -eq 128 ] && git init
    git remote add origin git@${db}.github:itove/db.$db
    # git push -u origin main
fi
