#!/bin/bash

if [ ! -x "./vendor/bin/sail" ]; then
    echo "Warning: sail not found - skipping formatters" >&2
    exit 0
fi

all_files=()
while IFS= read -r -d '' f; do
    all_files+=("$f")
done < <(git diff --cached -z --name-only --diff-filter=ACM)

php_files=()
js_files=()
for f in "${all_files[@]}"; do
    case "$f" in
        *.php)                       php_files+=("$f") ;;
        *.js|*.jsx|*.ts|*.tsx|*.vue) js_files+=("$f") ;;
    esac
done

if [ ${#php_files[@]} -gt 0 ]; then
    vendor/bin/sail bin pint "${php_files[@]}"
    git add "${php_files[@]}"
fi

if [ ${#js_files[@]} -gt 0 ]; then
    vendor/bin/sail npx prettier --write "${js_files[@]}"
    git add "${js_files[@]}"
fi

exit 0
