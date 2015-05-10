'use strict';

var _ = require('lodash');
var awstruct = require('awstruct');
var Bluebird = require('bluebird');

function methods(attributes) {
  var loadBalancerName = attributes.fullyQualifiedName;
  var federationName = awstruct.getResourceName('sgFederation');
  var params = { region: awstruct.region };
  var elbEx = awstruct.ex.elb(params);
  var elb = awstruct.sdk.elb(params);
  var ec2 = awstruct.sdk.ec2(params);

  function down() {
    return elbEx
      .doesLoadBalancerExist(loadBalancerName)
      .then(tearDown);
  }

  function tearDown(exists) {
    if (!exists) {
      return;
    }

    return elb
      .deleteLoadBalancerPromised({LoadBalancerName: loadBalancerName });
  }

  function up() {
    return elbEx
      .doesLoadBalancerExist(loadBalancerName)
      .then(setUp);
  }

  function setUp(exists) {
    if (exists) {
      return;
    }

    return Bluebird
      .resolve([
        describeAvailabilityZones(),
        describeFederationSecurityGroup()
      ])
      .spread(createLoadBalancer)
      .then(configureHealthCheck);
  }

  function describeAvailabilityZones() {
    return ec2.describeAvailabilityZonesPromised();
  }

  function describeFederationSecurityGroup() {
    return ec2.describeSecurityGroupsPromised({
      GroupNames: [federationName]
    });
  }

  function createLoadBalancer(zoneData, sgData) {
    var availabilityZoneNames = _.pluck(zoneData.AvailabilityZones, 'ZoneName');
    var securityGroupIds = _.pluck(sgData.SecurityGroups, 'GroupId');

    var params = {
      Listeners: [
        {
          InstancePort: 3000,
          LoadBalancerPort: 80,
          Protocol: 'HTTP'
        }
      ],
      LoadBalancerName: loadBalancerName,
      AvailabilityZones: availabilityZoneNames,
      SecurityGroups: securityGroupIds
    };

    return elb.createLoadBalancerPromised(params);
  }

  function configureHealthCheck() {
    var params = {
      HealthCheck: {
        HealthyThreshold: 5,
        Interval: 5,
        Target: 'HTTP:3000/healthcheck',
        Timeout: 4,
        UnhealthyThreshold: 5
      },
      LoadBalancerName: loadBalancerName
    };

    return elb.configureHealthCheckPromised(params);
  }

  return {
    down: down,
    up: up
  };
}

module.exports = awstruct.resource({
  name: 'elbApi',
  type: 'LoadBalancer'
}, methods);
