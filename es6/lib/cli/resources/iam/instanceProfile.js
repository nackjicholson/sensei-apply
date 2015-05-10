import awstruct from 'awstruct';

function methods(attributes) {
  let instanceProfileName = attributes.fullyQualifiedName;
  let roleName = awstruct.getResourceName('roleApiServer');
  let params = { region: awstruct.region };
  let iamEx = awstruct.ex.iam(params);
  let iam = awstruct.sdk.iam(params);

  function tearDown(exists) {
    if (!exists) {
      return;
    }

    removeRoleFromInstanceProfile()
      .then(deleteInstanceProfile);
  }

  function removeRoleFromInstanceProfile() {
    return iam.removeRoleFromInstanceProfilePromised({
      InstanceProfileName: instanceProfileName,
      RoleName: roleName
    });
  }

  function deleteInstanceProfile() {
    return iam.deleteInstanceProfilePromised({
      InstanceProfileName: instanceProfileName
    });
  }

  function createInstanceProfile(exists) {
    if (exists) {
      return;
    }

    return iam
      .createInstanceProfilePromised({
        InstanceProfileName: instanceProfileName
      })
      .then(addRoleToInstanceProfile);
  }

  function addRoleToInstanceProfile() {
    return iam.addRoleToInstanceProfilePromised({
      InstanceProfileName: instanceProfileName,
      RoleName: roleName
    });
  }

  return {
    /**
     * If the instanceProfile is present it is deleted.
     *
     * @returns {Promise}
     */
    down() {
      return iamEx
        .doesInstanceProfileExist(instanceProfileName)
        .then(tearDown);
    },

    /**
     * If the instanceProfile is not present it is created and has the role
     * added to it.
     *
     * @returns {Promise}
     */
    up() {
      return iamEx
        .doesInstanceProfileExist(instanceProfileName)
        .then(createInstanceProfile);
    }
  };
}

export default awstruct.resource({
  name: 'profileApiServers',
  type: 'InstanceProfile'
}, methods);
