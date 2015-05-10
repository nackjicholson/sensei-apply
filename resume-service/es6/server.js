require('babel/register');

import {Server} from 'hapi';
import loadPlugins from './lib/loadPlugins';

let server = new Server();
server.connection({ port: 9000 });

function startServer() {
  server.start(() => {
    server.log('info', 'Server running at: ' + server.info.uri);
  });
}

function logErrors(err) {
  server.log('error', err);
}

loadPlugins(server)
  .then(startServer)
  .catch(logErrors);
