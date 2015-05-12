import awstruct from 'awstruct';
//const handleEventually = awstruct.util.handleEventually;

function methods(attributes) {
  const branch = attributes.branch;
  const keyName = attributes.keyName;
  const securityGroups = attributes.securityGroups;
  const serverName = attributes.fullyQualifiedName;
  const instanceProfileName = awstruct.getResourceName('profileApiServers');
  const federationName = awstruct.getResourceName('sgFederation');
  const params = { region: awstruct.region };
  const ec2 = awstruct.sdk.ec2(params);
  const ec2Ex = awstruct.ex.ec2(params);

  function setUp(exists) {
    if (exists) {
      return;
    }

    const templatePath = path.resolve(__dirname, 'templates/userData.swig');
    const userData = swig.renderFile(templatePath, {branch});

    return ec2
      .runInstances({
        ImageId: 'ami-e7527ed7',
        MaxCount: 1,
        MinCount: 1,
        //ClientToken: 'STRING_VALUE',
        IamInstanceProfile: { Name: instanceProfileName },
        InstanceType: 't2.micro',
        KeyName: keyName,
        SecurityGroupIds: [federationName, ...securityGroups],
        UserData: 'STRING_VALUE'
      })
  }

  return {
    /**
     * If the instance is present it is deleted.
     *
     * @returns {Promise}
     */
    down() {
    },

    /**
     * If the instance is not present it is created.
     *
     * @returns {Promise}
     */
    up() {
      // check if the instance exists
      // runinstances
      // create tags
      return ec2Ex
        .doesInstanceExist(serverName)
        .then(setUp);
    }
  }
}

export default awstruct.resource({
  name: 'ec2ApiServer',
  type: 'Instance'
}, methods);
