import Boom from 'boom';
import Joi from 'joi';
import generoute from '../../util/generoute';
import uploadToSlack from '../uploadToSlack';

function* storeResume(request) {
  let success = {
    message: 'Thank you, we received your resume.'
  };

  let uploadPromise = uploadToSlack(request.payload)
    .then(() => success)
    .catch(err => Boom.wrap(err));

  return yield uploadPromise;
}

export default {
  method: 'POST',
  path: '/resumes',
  handler: generoute(storeResume),
  config: {
    payload: {
      maxBytes: 209715200,
      output: 'file',
      parse: true
    },
    validate: {
      payload: {
        name: Joi.string().max(70).required(),
        resume: Joi.object().required(),
        blurb: Joi.string().max(140)
      }
    }
  }
};
