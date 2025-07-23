#!/usr/bin/env bash

git update-index -q --ignore-submodules --refresh

err=0

# Disallow unstaged changes in the working tree
if ! git diff-files --quiet --ignore-submodules --
then
    echo "Error: you have unstaged changes."

    err=1
fi

# Disallow uncommitted changes in the index
if ! git diff-index --cached --quiet HEAD --ignore-submodules --
then
    echo "Error: your index contains uncommitted changes."

    err=1
fi

if [ $err = 1 ]
then
    echo ""

    git status >&2

    echo ""
    echo "Please commit or stash them."

    exit 1
fi

php vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix >&2

git add .

git commit -m "PHP CS Fixer" >&2
