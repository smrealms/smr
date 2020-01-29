#!/bin/bash

# Checks that every (non-template) PHP file starts with a "strict_types"
# declaration, since this is easy to accidentally omit.

ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"

ERROR="false"
while IFS= read -d '' FILE;
do
    LINE=$(head -n 1 "$FILE")

    if [[ "$LINE" != "<?php declare(strict_types=1);" ]] ; then
        echo "====> $FILE"
        echo "$LINE"
        ERROR="true"
    fi
done < <(find $ROOT/admin $ROOT/engine $ROOT/lib -type f \( -name "*.php" -o -name "*.inc" \) -print0)

if [[ "$ERROR" == "true" ]] ; then
    exit 1
else
    echo "Success! No strict_type errors."
    exit 0
fi
