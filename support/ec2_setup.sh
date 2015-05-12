#!/bin/bash
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.25.1/install.sh | bash
. ~/.nvm/nvm.sh
nvm install iojs
npm install -g pm2

cd /var/cascade/sensei-apply
npm install --production
pm2 start es6/server.js --next-gen-js -i 2
