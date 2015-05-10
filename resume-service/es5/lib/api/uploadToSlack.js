'use strict';

var _Object$defineProperty = require('babel-runtime/core-js/object/define-property')['default'];

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

_Object$defineProperty(exports, '__esModule', {
  value: true
});

var _bluebird = require('bluebird');

var _bluebird2 = _interopRequireDefault(_bluebird);

var _config = require('config');

var _config2 = _interopRequireDefault(_config);

var _fs = require('fs');

var _formData = require('form-data');

var _formData2 = _interopRequireDefault(_formData);

var TOKEN = _config2['default'].get('slack.token');
var CHANNELS = _config2['default'].get('slack.channels');
var UPLOAD_URL = _config2['default'].get('slack.uploadUrl');

function uploadToSlack(payload) {
  return new _bluebird2['default'](function (resolve, reject) {
    var form = new _formData2['default']();
    form.append('token', TOKEN);
    form.append('channels', CHANNELS);
    form.append('file', _fs.createReadStream(payload.resume.path));
    form.append('filename', payload.resume.filename);
    form.append('title', payload.name);
    form.append('initial_comment', payload.blurb);

    form.submit(UPLOAD_URL, function (err, res) {
      if (err) {
        reject(err);
        return;
      }

      resolve(res);
    });
  });
}

exports['default'] = uploadToSlack;
module.exports = exports['default'];