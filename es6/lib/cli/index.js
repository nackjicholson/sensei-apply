import nomnom from 'nomnom';
import down from './commands/down';
import up from './commands/up';
import {version} from '../../../package.json';

nomnom
  .options({
    keyName: {
      abbr: 'k',
      full: 'key-name',
      help: 'Name of the key to use for instances.'
    },
    region: {
      abbr: 'r',
      default: 'us-west-2',
      help: 'The region to deploy to.'
    },
    securityGroups: {
      abbr: 'g',
      full: 'security-groups',
      list: true,
      default: [],
      help: 'List of security groups to add to the launch configuration.'
    },
    system: {
      abbr: 's',
      default: 'senseiApply',
      help: 'The name of the system.'
    }
  });

nomnom
  .command('down')
  .help('Tear down the sensei-apply infrastructure.')
  .callback(down);

nomnom
  .command('up')
  .help('Spin up the sensei-apply infrastructure.')
  .callback(up);

nomnom.nom();

