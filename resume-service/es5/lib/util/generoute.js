'use strict';

Object.defineProperty(exports, '__esModule', {
  value: true
});

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

var _co = require('co');

var _co2 = _interopRequireDefault(_co);

// Wraps generator so it can be used in Hapi responses
function generoute(generator) {
  var handler = _co2['default'].wrap(generator);
  return function (request, reply) {
    handler.bind(this)(request).then(reply)['catch'](reply);
  };
}

exports['default'] = generoute;
module.exports = exports['default'];