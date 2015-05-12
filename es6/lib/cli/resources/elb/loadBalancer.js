import {map, pluck, reduce} from 'lodash';
import awstruct from 'awstruct';
import Bluebird from 'bluebird';

const handleEventually = awstruct.util.handleEventually;

function methods({fullyQualifiedName: loadBalancerName}) {
  const federationName = awstruct.getResourceName('sgFederation');
  const serverTag = awstruct.getResourceName('ec2ApiServer');
  const params = { region: awstruct.region };
  const elbEx = awstruct.ex.elb(params);
  const elb = awstruct.sdk.elb(params);
  const ec2 = awstruct.sdk.ec2(params);

  function tearDown(exists) {
    if (!exists) {
      return;
    }

    return elb
      .deleteLoadBalancerPromised({LoadBalancerName: loadBalancerName });
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
      .then(configureHealthCheck)
      .then(handleEventually(registerInstances));
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
    const availabilityZoneNames = pluck(zoneData.AvailabilityZones, 'ZoneName');
    const securityGroupIds = pluck(sgData.SecurityGroups, 'GroupId');

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
        Target: 'HTTP:9000/health',
        Timeout: 4,
        UnhealthyThreshold: 5
      },
      LoadBalancerName: loadBalancerName
    };

    return elb.configureHealthCheckPromised(params);
  }

  function registerInstances() {
    return describeInstances()
      .then((data) => {
        const instanceIds = reduce(data.Reservations, (result, reservation) => {
          const idList = pluck(reservation.Instances, 'InstanceId');
          const idCollection = map(idList, id => ({InstanceId: id}));
          result.push(...idCollection);
          return result;
        }, []);

        return elb.registerInstancesWithLoadBalancerPromised({
          Instances: instanceIds,
          LoadBalancerName: loadBalancerName
        });
      });
  }

  function describeInstances() {
    return ec2.describeInstancesPromised({
      Filters: [
        {
          Name: 'instance-state-name',
          Values: ['running']
        },
        {
          Name: 'tag:Name',
          Values: [serverTag]
        }
      ]
    });
  }

  return {
    down() {
      return elbEx
        .doesLoadBalancerExist(loadBalancerName)
        .then(tearDown);
    },
    up() {
      return elbEx
        .doesLoadBalancerExist(loadBalancerName)
        .then(setUp);
    }
  };
}

export default awstruct.resource({
  name: 'elbApi',
  type: 'LoadBalancer'
}, methods);
