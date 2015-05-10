'use strict';

var _Object$defineProperty = require('babel-runtime/core-js/object/define-property')['default'];

var _regeneratorRuntime = require('babel-runtime/regenerator')['default'];

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

_Object$defineProperty(exports, '__esModule', {
  value: true
});

var marked0$0 = [storeResume].map(_regeneratorRuntime.mark);

var _boom = require('boom');

var _boom2 = _interopRequireDefault(_boom);

var _joi = require('joi');

var _joi2 = _interopRequireDefault(_joi);

var _utilGeneroute = require('../../util/generoute');

var _utilGeneroute2 = _interopRequireDefault(_utilGeneroute);

var _uploadToSlack = require('../uploadToSlack');

var _uploadToSlack2 = _interopRequireDefault(_uploadToSlack);

function storeResume(request) {
  var success, uploadPromise;
  return _regeneratorRuntime.wrap(function storeResume$(context$1$0) {
    while (1) switch (context$1$0.prev = context$1$0.next) {
      case 0:
        success = {
          message: 'Thank you, we received your resume.'
        };
        uploadPromise = _uploadToSlack2['default'](request.payload).then(function () {
          return success;
        })['catch'](function (err) {
          return _boom2['default'].wrap(err);
        });
        context$1$0.next = 4;
        return uploadPromise;

      case 4:
        return context$1$0.abrupt('return', context$1$0.sent);

      case 5:
      case 'end':
        return context$1$0.stop();
    }
  }, marked0$0[0], this);
}

exports['default'] = {
  method: 'POST',
  path: '/resumes',
  handler: _utilGeneroute2['default'](storeResume),
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
};
module.exports = exports['default'];