'use strict';

var _Object$defineProperty = require('babel-runtime/core-js/object/define-property')['default'];

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

_Object$defineProperty(exports, '__esModule', {
  value: true
});

var _routesApply = require('./routes/apply');

var _routesApply2 = _interopRequireDefault(_routesApply);

function register(server, options, next) {
  server.route([_routesApply2['default']]);
  next();
}

register.attributes = {
  name: 'resume-service-api'
};

exports['default'] = register;
module.exports = exports['default'];