import awstruct from 'awstruct';
import chalksay from 'chalksay';
import federation from '../resources/ec2/federation';
import instanceProfile from '../resources/iam/instanceProfile';
import role from '../resources/iam/role';

function up(options) {
  awstruct.region = options.region;
  awstruct.system = options.system;

  function buildInfrastructure() {
    let manager = awstruct.resourceManager([
      role(),
      instanceProfile(),
      federation()
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
