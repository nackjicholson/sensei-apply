'use strict';

Object.defineProperty(exports, '__esModule', {
  value: true
});

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

var _boom = require('boom');

var _boom2 = _interopRequireDefault(_boom);

var _config = require('config');

var _config2 = _interopRequireDefault(_config);

var _fs = require('fs');

//import Slack from 'slack-node';

var _formData = require('form-data');

var _formData2 = _interopRequireDefault(_formData);

//import got from 'got-promise';

//let token = config.get('slack.token');
//let channels = config.get('slack.channels');
var slack = _config2['default'].get('slack');

function applyHandler(request, reply) {
  var resume = request.payload.resume;
  var name = request.payload.name;
  var blurb = request.payload.blurb;

  var form = new _formData2['default']();
  form.append('token', slack.token);
  form.append('channels', slack.channels);
  form.append('file', _fs.createReadStream(resume.path));
  form.append('filename', resume.filename);
  form.append('title', name);

  if (blurb) {
    form.append('initial_comment', blurb);
  }

  form.submit(slack.uploadUrl, function (err) {
    if (err) {
      return reply(_boom2['default'].wrap(err));
    }

    return reply({ message: 'Thank you, we received your resume.' });
  });
}

exports['default'] = applyHandler;
module.exports = exports['default'];