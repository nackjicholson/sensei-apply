#!/bin/bash
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.25.1/install.sh | bash
. ~/.nvm/nvm.sh
nvm install iojs
npm install -g babel

cd /var/cascade/sensei-apply
npm install --production
babel-node es6/server.js
