import store from './routes/store';

function register(server, options, next) {
  server.route([store]);
  next();
}

register.attributes = {
  name: 'resume-service-api'
};

export default register;
