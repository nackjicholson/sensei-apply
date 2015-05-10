'use strict';

var _Object$defineProperty = require('babel-runtime/core-js/object/define-property')['default'];

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

_Object$defineProperty(exports, '__esModule', {
  value: true
});

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