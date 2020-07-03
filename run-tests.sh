#!/bin/sh

cd node || exit
npm install
cd ..

nohup node node/proxy.js &
echo $! > pid1.txt

cd tests || exit
# shellcheck disable=SC2046
../vendor/bin/phpunit --configuration phpunit.config.xml . && kill -9 $(cat ../pid1.txt) && rm -f ../pid1.txt ../nohup.out
