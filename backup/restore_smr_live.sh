#!/usr/bin/env bash

set -eo pipefail

base_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." >/dev/null && pwd)"
cd "${base_dir}" || exit

backup_dir="${base_dir}/backup/daily"

# Take the given parameter as dump or find the youngest backup file in the backup dir
backup_file="${1:-$(cd "${backup_dir}" && ls -1t *.bz2 | head -n 1)}"

# Check if we have a valid file
if [[ ! -f "${backup_dir}/${backup_file}" ]]; then
    echo "Unable to find a valid backup file. Check ${backup_dir}"
    exit 1
fi

echo "Import '${backup_file}' into database..."
echo "Hint: You can also provide a dump via command line parameter, eg: ${0} smr_live_2018-11-28.sql.bz2"

# Unzip dump and import on the fly
bzcat --keep "${backup_dir}/${backup_file}" | docker compose exec -T mysql mysql
