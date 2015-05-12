import awstruct from 'awstruct';
import chalksay from 'chalksay';
import federation from '../resources/ec2/federation';
import instance from '../resources/ec2/instance';
import instanceProfile from '../resources/iam/instanceProfile';
import role from '../resources/iam/role';

function down(options) {
  awstruct.region = options.region;
  awstruct.system = options.system;

  function destroyInfrastructure() {
    var manager = awstruct.resourceManager([
      instance(),
      instanceProfile(),
      role(),
      federation()
    ]);

    chalksay.blue('Tearing down sensei-apply infrastructure.\n');
    return manager.down();
  }

  function throwError(err) {
    throw err;
  }

  destroyInfrastructure().catch(throwError);
}

module.exports = down;
