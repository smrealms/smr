#!/usr/bin/env bash

set -eo pipefail

base_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." >/dev/null && pwd)"
cd "${base_dir}" || exit

backup_dir="${base_dir}/backup/archive"

# Take the given parameter as dump or find the youngest backup file in the backup dir
backup_file="${1:?"Please provide a dump file name."}"

# Check if we have a valid file
if [[ ! -f "${backup_dir}/${backup_file}" ]]; then
    echo "Unable to find a valid backup file. Check ${backup_dir}"
    exit 1
fi

echo "Import '${backup_file}' into database..."

# Unzip dump and import on the fly
bzcat --keep "${backup_dir}/${backup_file}" | docker compose exec -T mysql mysql
