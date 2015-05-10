'use strict';

var _Object$defineProperty = require('babel-runtime/core-js/object/define-property')['default'];

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

_Object$defineProperty(exports, '__esModule', {
  value: true
});

var _gotPromise = require('got-promise');

var _gotPromise2 = _interopRequireDefault(_gotPromise);

var _lodashCollectionSample = require('lodash/collection/sample');

var _lodashCollectionSample2 = _interopRequireDefault(_lodashCollectionSample);

function serviceLookup(serviceName, routePath) {
  //let serviceHealthUrl = `http://internal.consul.energysensei.info
  var serviceHealthUrl = 'http://172.21.70.9:8500/v1/health/service/' + serviceName + '?passing'; // jshint ignore:line

  function composeServiceUrl(response) {
    console.log(response.body);
    var service = _lodashCollectionSample2['default'](response.body).Service;
    return 'http://' + service.Address + ':' + service.Port + '' + routePath + '}}';
  }

  return _gotPromise2['default'].get(serviceHealthUrl, { json: true }).then(composeServiceUrl);
}

exports['default'] = serviceLookup;
module.exports = exports['default'];