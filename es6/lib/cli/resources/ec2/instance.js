//import awstruct from 'awstruct';
//
//function methods(attributes) {
//  let serverName = attributes.fullyQualifiedName;
//  let instanceProfileName = awstruct.getResourceName('profileApiServers');
//  let federationName = awstruct.getResourceName('sgFederation');
//  let params = { region: awstruct.region };
//  let ec2 = awstruct.sdk.ec2(params);
//
//  return {
//    /**
//     * If the instance is present it is deleted.
//     *
//     * @returns {Promise}
//     */
//    down() {
//    },
//
//    /**
//     * If the instance is not present it is created.
//     *
//     * @returns {Promise}
//     */
//    up() {
//      // runinstances
//      // create tags
//
//    }
//  }
//}
//
//export default awstruct.resource({
//  name: 'ec2ApiServer',
//  type: 'Instance'
//}, methods);
