FROM iojs:slim

EXPOSE 9000

ADD . /var/cascade/sensei-apply/resume-service

WORKDIR /var/cascade/sensei-apply/resume-service

RUN apt-get update
RUN npm install --production

ENTRYPOINT node es5/server.js
