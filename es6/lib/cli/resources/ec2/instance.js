import awstruct from 'awstruct';
import path from 'path';
import fs from 'fs';
import {pluck, reduce} from 'lodash';

const handleEventually = awstruct.util.handleEventually;

function methods({keyName, securityGroups, fullyQualifiedName}) {
  const instanceProfileName = awstruct.getResourceName('profileApiServers');
  const federationName = awstruct.getResourceName('sgFederation');
  const params = { region: awstruct.region };
  const ec2 = awstruct.sdk.ec2(params);
  const ec2Ex = awstruct.ex.ec2(params);

  function setUp(exists) {
    if (exists) {
      return;
    }

    // TODO Remove this hack
    // There is an issue with the instanceProfile not yet being fully available
    // from a previous step. So I'm wrapping this kickoff in a handleEventually
    // call. This feels like the wrong place, the waiting should happen at the
    // end of creating the instance profile, it shouldn't leave that resource
    // until the resource is available I may need a waitUntilAvailable utility.
    return handleEventually(runInstances)()
      .then(createTags);
  }

  function runInstances() {
    const userDataPath = path.resolve(__dirname, 'templates/userData.sh');
    const userData = fs.readFileSync(userDataPath);

    return ec2
      .runInstancesPromised({
        ImageId: 'ami-e7527ed7',
        MaxCount: 1,
        MinCount: 1,
        IamInstanceProfile: { Name: instanceProfileName },
        InstanceType: 't2.micro',
        KeyName: keyName,
        SecurityGroupIds: [federationName, ...securityGroups],
        UserData: new Buffer(userData).toString('base64')
      });
  }

  function createTags(data) {
    const instanceIds = pluck(data.Instances, 'InstanceId');

    return ec2.createTagsPromised({
      Resources: instanceIds,
      Tags: [
        {
          Key: 'Name',
          Value: fullyQualifiedName
        }
      ]
    });
  }

  function tearDown(exists) {
    if (!exists) {
      return;
    }

    return describeInstances()
      .then(terminateInstances);
  }

  function describeInstances() {
    return ec2.describeInstancesPromised({
      Filters: [
        {
          Name: 'tag:Name',
          Values: [fullyQualifiedName]
        }
      ]
    });
  }

  function terminateInstances(data) {
    const instanceIds = reduce(data.Reservations, (result, reservation) => {
      const ids = pluck(reservation.Instances, 'InstanceId');
      result.push(...ids);
      return result;
    }, []);

    return ec2.terminateInstancesPromised({InstanceIds: instanceIds});
  }

  return {
    /**
     * If the instance is present it is deleted.
     *
     * @returns {Promise}
     */
    down() {
      return ec2Ex
        .doesInstanceExist(fullyQualifiedName, 'running')
        .then(tearDown);
    },

    /**
     * If the instance is not present it is created.
     *
     * @returns {Promise}
     */
    up() {
      return ec2Ex
        .doesInstanceExist(fullyQualifiedName, 'running')
        .then(setUp);
    }
  };
}

export default awstruct.resource({
  name: 'ec2ApiServer',
  type: 'Instance'
}, methods);
