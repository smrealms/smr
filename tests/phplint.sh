#!/bin/bash

# Runs `php -l` on each *.php and *.inc file in the repository.
# This performs basic linting checks.

ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"

ERROR="false"
while IFS= read -d '' FILE;
do
    RESULTS=`php -l "$FILE" 2>&1`

    if [[ "$RESULTS" != "No syntax errors detected in $FILE" ]] ; then
        echo "====> $FILE"
        echo "$RESULTS"
        ERROR="true"
    fi
done < <(find $ROOT -type f -name "*.php" -o -name "*.inc" -print0)

if [[ "$ERROR" == "true" ]] ; then
    exit 1
else
    echo "Success! No linting errors."
    exit 0
fi
