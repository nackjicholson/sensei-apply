#!/bin/bash -xe
yum update -y
yum install git -y

# Deploy code.
mkdir -p /var/cascade

cd /var/cascade
git clone https://github.com/CascadeEnergy/sensei-apply.git
chown -R ec2-user:ec2-user /var/cascade

