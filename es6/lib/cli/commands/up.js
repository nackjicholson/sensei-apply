import awstruct from 'awstruct';
import chalksay from 'chalksay';
import federation from '../resources/ec2/federation';
import instance from '../resources/ec2/instance';
import instanceProfile from '../resources/iam/instanceProfile';
import loadBalancer from '../resources/elb/loadBalancer';
import role from '../resources/iam/role';

function up(options) {
  awstruct.region = options.region;
  awstruct.system = options.system;

  function buildInfrastructure() {
    let manager = awstruct.resourceManager([
      role(),
      instanceProfile(),
      federation(),
      instance({
        keyName: options.keyName,
        securityGroups: options.securityGroups,
        branch: options.branch
      }),
      loadBalancer()
    ]);

    chalksay.blue('\nSpinning up sensei-apply infrastructure.\n');
    return manager.up();
  }

  function throwError(err) {
    throw err;
  }

  // Spin up the infrastructure
  buildInfrastructure().catch(throwError);
}

export default up;
