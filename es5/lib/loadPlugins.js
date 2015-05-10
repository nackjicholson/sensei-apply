'use strict';

var _Object$defineProperty = require('babel-runtime/core-js/object/define-property')['default'];

var _interopRequireDefault = require('babel-runtime/helpers/interop-require-default')['default'];

_Object$defineProperty(exports, '__esModule', {
  value: true
});

var _bluebird = require('bluebird');

var _bluebird2 = _interopRequireDefault(_bluebird);

var _good = require('good');

var _good2 = _interopRequireDefault(_good);

var _goodConsole = require('good-console');

var _goodConsole2 = _interopRequireDefault(_goodConsole);

var _hapiPkg = require('hapi-pkg');

var _hapiPkg2 = _interopRequireDefault(_hapiPkg);

var _packageJson = require('../../package.json');

var _packageJson2 = _interopRequireDefault(_packageJson);

var _apiIndex = require('./api/index');

var _apiIndex2 = _interopRequireDefault(_apiIndex);

/**
 * Loads plugins and returns a promise is resolved when all of the plugins
 * are finished loading. The promise is rejected if any errors occur.
 *
 * - Good / GoodConsole app logging
 * - The hapi-pkg plugin
 * - The Resume Service Api plugin
 *
 * @param server
 * @returns {Bluebird}
 */
function loadPlugins(server) {
  return new _bluebird2['default'](function (resolve, reject) {
    server.register([{
      register: _good2['default'],
      options: {
        reporters: [{
          reporter: _goodConsole2['default'],
          events: {
            response: '*',
            log: '*'
          }
        }]
      }
    }, {
      register: _hapiPkg2['default'],
      options: {
        endpoint: 'info',
        pkg: _packageJson2['default']
      }
    }, {
      register: _apiIndex2['default']
    }], function onRegistrationComplete(err) {
      if (err) {
        reject(err);
      } else {
        resolve();
      }
    });
  });
}

exports['default'] = loadPlugins;
module.exports = exports['default'];