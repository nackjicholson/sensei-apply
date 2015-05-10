'use strict';

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

var _hapi = require('hapi');

var _libLoadPlugins = require('./lib/loadPlugins');

var _libLoadPlugins2 = _interopRequireDefault(_libLoadPlugins);

require('babel/register');

var server = new _hapi.Server();
server.connection({ port: 9000 });

function startServer() {
  server.start(function () {
    server.log('info', 'Server running at: ' + server.info.uri);
  });
}

function logErrors(err) {
  server.log('error', err);
}

_libLoadPlugins2['default'](server).then(startServer)['catch'](logErrors);