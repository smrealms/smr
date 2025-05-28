#!/bin/bash -e

# cd to root directory of this repository
cd "$(dirname $0)/.."

backup_dir="backup/manual"
mkdir -p "${backup_dir}"

today="$(date +'%Y-%m-%d_%H-%M')"
backup_file="${backup_dir}/smr_live_${today}.sql"

echo "Backing up to ${backup}"
docker compose exec -T mysql mysqldump --no-tablespaces --add-drop-database --databases smr_live > ${backup_file}

# To restore:
# cat backup.sql | docker compose exec -T mysql mysql smr_live
