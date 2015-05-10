'use strict';

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

var _hapi = require('hapi');

var _hapiPkg = require('hapi-pkg');

var _hapiPkg2 = _interopRequireDefault(_hapiPkg);

var _good = require('good');

var _good2 = _interopRequireDefault(_good);

var _goodConsole = require('good-console');

var _goodConsole2 = _interopRequireDefault(_goodConsole);

var _libServiceLookup = require('./lib/serviceLookup');

var _libServiceLookup2 = _interopRequireDefault(_libServiceLookup);

var _packageJson = require('../package.json');

var _packageJson2 = _interopRequireDefault(_packageJson);

var server = new _hapi.Server();
server.connection({ port: 8000 });

server.route({
  method: 'POST',
  path: '/apply',
  handler: function handler(request, reply) {
    return _libServiceLookup2['default']('resume', '/resumes').then(function (uri) {
      return reply.proxy({ uri: uri });
    });
  }
});

server.register([{
  register: _good2['default'],
  options: {
    reporters: [{
      reporter: _goodConsole2['default'],
      events: {
        response: '*',
        log: '*'
      }
    }]
  }
}, {
  register: _hapiPkg2['default'],
  options: {
    endpoint: 'info',
    pkg: _packageJson2['default']
  }
}], function startServer(err) {
  if (err) {
    server.log('error', err);
  }

  server.start(function () {
    server.log('info', 'Server running at: ' + server.info.uri);
  });
});