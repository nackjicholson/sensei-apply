'use strict';

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

var _hapi = require('hapi');

var _hapiPkg = require('hapi-pkg');

var _hapiPkg2 = _interopRequireDefault(_hapiPkg);

var _packageJson = require('../package.json');

var _packageJson2 = _interopRequireDefault(_packageJson);

require('babel/register');

var server = new _hapi.Server();

server.connection({ port: 9000 });

server.register({
  register: _hapiPkg2['default'],
  options: { pkg: _packageJson2['default'], endpoint: 'info' }
}, function (err) {
  if (err) {
    throw err;
  }

  server.start(function () {
    console.log('Server running at:', server.info.uri);
  });
});