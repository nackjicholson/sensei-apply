import Boom from 'boom';
import Joi from 'joi';
import generoute from '../../util/generoute';
import uploadToSlack from '../uploadToSlack';

/**
 * Stores resume by uploading it to slack channel.
 *
 * @param request
 * @returns {*}
 */
function* storeResume(request) {
  let success = {
    message: 'Thank you, we received your resume.'
  };

  let uploadPromise = uploadToSlack(request.payload)
    .then(() => success)
    .catch(err => Boom.wrap(err));

  return yield uploadPromise;
}

const description = `Put yourself at the top of our list by sending a resume to
our Slack channel. Give us your name, a resume file, and a short message.`;

const notes = `When we contact you, we may ask what method you used to
hit our API. Here's one way to do it.

<pre>
  curl -F resume=@bm.pdf -F name="Bill Murray" -F blurb="gunga gulunga"
</pre>`;

export default {
  method: 'POST',
  path: '/resumes',
  handler: generoute(storeResume),
  config: {
    description: description,
    notes: notes,
    payload: {
      maxBytes: 209715200,
      output: 'file',
      parse: true
    },
    validate: {
      payload: {
        name: Joi
          .string()
          .required()
          .description('Your full name')
          .max(70),
        resume: Joi
          .any()
          .required()
          .description('A file posted as multipart/form-data'),
        blurb: Joi
          .string()
          .optional()
          .description(
            `Say whatever you want here, the message is
            posted with your file to our slack channel.`
          )
          .max(140)
      }
    }
  }
};
