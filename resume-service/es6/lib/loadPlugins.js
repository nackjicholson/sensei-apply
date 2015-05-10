'use strict';
import Bluebird from 'bluebird';
import good from 'good';
import goodConsole from 'good-console';
import hapiPkg from 'hapi-pkg';
import pkg from '../../package.json';
import api from './api/index';

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
  return new Bluebird((resolve, reject) => {
    server.register([
      {
        register: good,
        options: {
          reporters: [{
            reporter: goodConsole,
            events: {
              response: '*',
              log: '*'
            }
          }]
        }
      },
      {
        register: hapiPkg,
        options: {
          endpoint: 'info',
          pkg
        }
      },
      {
        register: api
      }
    ], function onRegistrationComplete(err) {
      if (err) {
        reject(err);
      } else {
        resolve();
      }
    });
  });
}

export default loadPlugins;
