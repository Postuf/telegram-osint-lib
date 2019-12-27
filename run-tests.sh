#!/bin/sh

cd node
npm install
cd ..

nohup node node/proxy.js &
echo $! > pid1.txt

cd tests
../vendor/bin/phpunit --configuration phpunit.config.xml . && kill -9 `cat ../pid1.txt`
