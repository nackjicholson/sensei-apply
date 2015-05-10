#!/bin/bash -xe
yum update -y
yum install -y docker
service docker start
usermod -a -G docker ec2-user
docker pull {{ container }}:{{ tag }}
docker run --name=sensei-apply-hapi -d -p 3000:3000 --restart=always {{ container }}:{{ tag }}
