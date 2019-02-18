#!/bin/bash -e

# NOTICE: This script is only intended to be used for setting up a development
# installation for the first time, i.e. when following the README instructions.

# Initialize the first account as an admin.
# Will validate and grant "Manage Admins Permissions" permission.
docker-compose exec -T mysql sh -c 'mysql -u $MYSQL_USER -p$MYSQL_PASSWORD -e "use smr_live; REPLACE INTO account_has_permission (account_id, permission_id) VALUES (1, 1); UPDATE account SET validated=\"TRUE\" WHERE account_id=1;"'
