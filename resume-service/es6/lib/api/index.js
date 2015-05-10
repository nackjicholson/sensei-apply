import Joi from 'joi';
import applyHandler from './handlers/apply';

function register(server, options, next) {

  server.route([
    {
      method: 'POST',
      path: '/apply',
      handler: applyHandler,
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
    }
  ]);

  next();
}

register.attributes = {
  name: 'resume-service-api'
};

export default register;
