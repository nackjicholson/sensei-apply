import {Server} from 'hapi';
import hapiPkg from 'hapi-pkg';
import good from 'good';
import goodConsole from 'good-console';
import serviceLookup from './lib/serviceLookup';
import pkg from '../package.json';

let server = new Server();
server.connection({port: 8000});

server.route({
  method: 'POST',
  path: '/apply',
  handler(request, reply) {
    console.log(request);
    return serviceLookup('resume', '/resumes')
      .then(uri => reply.proxy({uri}));
  }
});

server.register([
  {
    register: good,
    options: {
      reporters: [{
        reporter: goodConsole,
        events: {
          response: '*',
          log: '*'
        }
      }]
    }
  },
  {
    register: hapiPkg,
    options: {
      endpoint: 'info',
      pkg
    }
  }
], function startServer(err) {
  if (err) {
    server.log('error', err);
  }

  server.start(() => {
    server.log('info', `Server running at: ${server.info.uri}`);
  });
});
