import Bluebird from 'bluebird';
import good from 'good';
import goodConsole from 'good-console';
import hapiPkg from 'hapi-pkg';
import lout from 'lout';
import {version} from '../../package.json';
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
        register: lout
      },
      {
        register: hapiPkg,
        options: {
          endpoint: 'health',
          pkg: { status: 'ok', version }
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
