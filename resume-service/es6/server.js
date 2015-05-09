require('babel/register');

import {Server} from 'hapi';
import hapiPkg from 'hapi-pkg';
import pkg from '../package.json';

let server = new Server();

server.connection({ port: 9000 });

server.register({
  register: hapiPkg,
  options: { pkg, endpoint: 'info' }
}, (err) => {
  if (err) {
    throw err;
  }

  server.start(() => {
    console.log('Server running at:', server.info.uri);
  });
});
