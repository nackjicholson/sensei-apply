import got from 'got-promise';
import sample from 'lodash/collection/sample';

function serviceLookup(serviceName, routePath) {
  let serviceHealthUrl = `http://http://internal.consul.energysensei.info/v1/health/service/${serviceName}?passing`; // jshint ignore:line

  function composeServiceUrl(response) {
    console.log(response.body);
    let service = sample(response.body).Service;
    return `http://${service.Address}:${service.Port}${routePath}}}`;
  }

  return got
    .get(serviceHealthUrl, {json: true})
    .then(composeServiceUrl);
}

export default serviceLookup;
