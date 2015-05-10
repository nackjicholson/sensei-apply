'use strict';

Object.defineProperty(exports, '__esModule', {
  value: true
});

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

var _joi = require('joi');

var _joi2 = _interopRequireDefault(_joi);

var _handlersApply = require('./handlers/apply');

var _handlersApply2 = _interopRequireDefault(_handlersApply);

function register(server, options, next) {

  server.route([{
    method: 'POST',
    path: '/apply',
    handler: _handlersApply2['default'],
    config: {
      payload: {
        maxBytes: 209715200,
        output: 'file',
        parse: true
      },
      validate: {
        payload: {
          name: _joi2['default'].string().max(70).required(),
          resume: _joi2['default'].object().required(),
          blurb: _joi2['default'].string().max(140)
        }
      }
    }
  }]);

  next();
}

register.attributes = {
  name: 'resume-service-api'
};

exports['default'] = register;
module.exports = exports['default'];