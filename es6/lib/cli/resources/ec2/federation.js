import awstruct from 'awstruct';
import Bluebird from 'bluebird';

let handleEventually = awstruct.util.handleEventually;

function methods(attributes) {
  let federationName = attributes.fullyQualifiedName;
  let params = { region: awstruct.region };
  let ec2Ex = awstruct.ex.ec2(params);
  let ec2 = awstruct.sdk.ec2(params);

  function deleteFederation(exists) {
    if (!exists) {
      return;
    }

    return ec2.deleteSecurityGroupPromised({ GroupName: federationName });
  }

  function createFederation(exists) {
    if (exists) {
      return;
    }

    return ec2
      .createSecurityGroupPromised({
        Description: 'Federated security group access for SenseiApply',
        GroupName: federationName
      })
      .then(addIngressRules);
  }

  function addIngressRules(data) {
    return Bluebird.resolve([
      addFederatedIngress(data.GroupId),
      addHttpIngress(data.GroupId)
    ]).all();
  }

  function addFederatedIngress(groupId) {
    return ec2
      .authorizeSecurityGroupIngressPromised({
        GroupId: groupId,
        SourceSecurityGroupName: federationName
      });
  }

  function addHttpIngress(groupId) {
    return ec2
      .authorizeSecurityGroupIngressPromised({
        CidrIp: '0.0.0.0/0',
        FromPort: 80,
        GroupId: groupId,
        IpProtocol: 'tcp',
        ToPort: 80
      });
  }

  return {
    /**
     * If the federation security group is present it is deleted.
     *
     * @returns {Promise}
     */
    down() {
      return ec2Ex
        .doesSecurityGroupExist(federationName)
        .then(handleEventually(deleteFederation, { ms: 3000 }));
    },

    /**
     * If the federation security group is not present it is created and
     * has ingress rules assigned to it.
     *
     * @returns {Promise}
     */
    up() {
      return ec2Ex
        .doesSecurityGroupExist(federationName)
        .then(createFederation);
    }
  };
}

export default awstruct.resource({
  name: 'sgFederation',
  type: 'SecurityGroup'
}, methods);
