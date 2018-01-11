#!/bin/bash

# Runs `php -l` on each *.php and *.inc file in the repository.
# This performs basic linting checks.

ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"

error=false
find $ROOT -type f -name "*.php" -o -name "*.inc" | while read FILE;
do
    RESULTS=`php -l "$FILE" 2>&1`

    if [ "$RESULTS" != "No syntax errors detected in $FILE" ] ; then
        echo "====> $FILE"
        echo "$RESULTS"
        error=true
    fi
done

if [ "$error" = true ] ; then
    exit 1
else
    echo "Success! No linting errors."
    exit 0
fi
