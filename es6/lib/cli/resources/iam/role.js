import awstruct from 'awstruct';

function methods(attributes) {
  let roleName = attributes.fullyQualifiedName;
  let params = { region: awstruct.region };
  let iamEx = awstruct.ex.iam(params);
  let iam = awstruct.sdk.iam(params);

  function deleteRole(exists) {
    if (!exists) {
      return;
    }

    return iam.deleteRolePromised({ RoleName: roleName });
  }

  function createRole(exists) {
    if (exists) {
      return;
    }

    return iam
      .createRolePromised({
        AssumeRolePolicyDocument: JSON.stringify({
          Version: '2012-10-17',
          Statement: [
            {
              Sid: 'allowEc2',
              Effect: 'Allow',
              Principal: {
                Service: 'ec2.amazonaws.com'
              },
              Action: 'sts:AssumeRole'
            }
          ]
        }),
        RoleName: roleName
      });
  }

  return {
    /**
     * If the role is present it is deleted.
     *
     * @returns {Promise}
     */
    down() {
      return iamEx
        .doesRoleExist(roleName)
        .then(deleteRole);
    },

    /**
     * If the role is not present it is created and this
     * method is resolved with IAM.createRole response data.
     *
     * @returns {Promise}
     */
    up() {
      return iamEx
        .doesRoleExist(roleName)
        .then(createRole);
    }
  };
}

export default awstruct.resource({
  name: 'roleApiServer',
  type: 'Role'
}, methods);
