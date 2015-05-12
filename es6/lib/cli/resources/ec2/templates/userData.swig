#!/bin/bash -xe
yum update -y
yum install -y nodejs npm --enablerepo=epel
yum install git -y
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.25.1/install.sh | bash
nvm install iojs-v2.0.1
npm install -g babel-node
mkdir -p /var/cascade
cd /var/cascade
git clone -b {{ branch }} git@github.com:CascadeEnergy/sensei-apply.git
chown -R ec2-user:ec2-user /var/cascade
cd /var/cascade/sensei-apply
babel-node es6/server.js
