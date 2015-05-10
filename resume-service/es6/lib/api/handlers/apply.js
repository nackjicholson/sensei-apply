import Boom from 'boom';
import config from 'config';
import {createReadStream} from 'fs';
import FormData from 'form-data';

let slack = config.get('slack');

function applyHandler(request, reply) {
  let resume = request.payload.resume;
  let name = request.payload.name;
  let blurb = request.payload.blurb;

  let form = new FormData();
  form.append('token', slack.token);
  form.append('channels', slack.channels);
  form.append('file', createReadStream(resume.path));
  form.append('filename', resume.filename);
  form.append('title', name);

  if (blurb) {
    form.append('initial_comment', blurb);
  }

  form.submit(slack.uploadUrl, (err) => {
    if (err) {
      return reply(Boom.wrap(err));
    }

    return reply({message: 'Thank you, we received your resume.'});
  });
}

export default applyHandler;
