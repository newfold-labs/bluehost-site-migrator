#!/usr/bin/env bash

rm -rf ./.git
rm -rf ./.github
rm -rf ./node_modules
rm -rf ./source
rm -rf ./vendor
find . -name '.*' -type f -maxdepth 1 -delete
rm composer.json
rm composer.lock
rm package.json
rm package-lock.json
rm phpcs.xml
rm readme.md
rm webpack.config.js
