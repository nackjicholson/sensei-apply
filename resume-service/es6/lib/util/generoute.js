import co from 'co';

// Wraps generator so it can be used in Hapi responses
function generoute(generator) {
  let handler = co.wrap(generator);
  return function(request, reply) {
    handler.bind(this)(request)
      .then(reply)
      .catch(reply);
  };
}

export default generoute;
