#!/bin/bash -xe

nvm install iojs
npm install -g babel-node

cd /var/cascade/sensei-apply
npm install --production
babel-node es6/server.js
