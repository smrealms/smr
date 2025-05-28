#!/bin/bash -e

# cd to root directory of this repository
cd "$(dirname $0)/.."

backup_dir="backup/daily"
mkdir -p "${backup_dir}"

today=$(date +%Y-%m-%d)
backup_file="${backup_dir}/smr_live_${today}.sql"

docker compose exec -T mysql mysqldump --no-tablespaces --add-drop-table --add-locks --quote-names --databases smr_live > ${backup_file}
bzip2 "${backup_file}"

# Delete all backups that are older than 7 days
# S3 bucket has it's own expiration of 14 days. To avoid uploading old backups we delete much sooner locally than on s3
find "${backup_dir}" -daystart -mtime +7 -delete
