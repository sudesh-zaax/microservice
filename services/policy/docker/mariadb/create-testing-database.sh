#!/usr/bin/env bash

/usr/bin/mariadb --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;
    GRANT ALL PRIVILEGES ON \`$MYSQL_DATABASE%\`.* TO '$MYSQL_USER'@'%';
EOSQL
