import Bluebird from 'bluebird';
import config from 'config';
import {createReadStream} from 'fs';
import FormData from 'form-data';

const TOKEN = config.get('slack.token');
const CHANNELS = config.get('slack.channels');
const UPLOAD_URL = 'https://slack.com/api/files.upload';

function uploadToSlack(payload) {
  return new Bluebird((resolve, reject) => {
    let form = new FormData();
    form.append('token', TOKEN);
    form.append('channels', CHANNELS);
    form.append('file', createReadStream(payload.resume.path));
    form.append('filename', payload.resume.filename);
    form.append('title', payload.name);
    form.append('initial_comment', payload.blurb);

    form.submit(UPLOAD_URL, (err, res) => {
      if (err) {
        reject(err);
        return;
      }

      resolve(res);
    });
  });
}

export default uploadToSlack;
