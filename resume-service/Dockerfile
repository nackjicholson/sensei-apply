FROM iojs:slim

EXPOSE 9000

ADD . /var/cascade/sensei-apply/resume-service

WORKDIR /var/cascade/sensei-apply/resume-service

RUN apt-get update
RUN npm install --production

ENV SERVICE_9000_CHECK_SCRIPT="curl --fail http://$SERVICE_IP:$SERVICE_PORT/info/version"
ENV SERVICE_9000_NAME=resume

ENTRYPOINT node es5/server.js
