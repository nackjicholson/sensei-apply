<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\S3\S3Client;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use Nack\FileParser\FileParser;
use Nack\Monolog\Handler\GitterImHandler;
use SenseiApply\Controllers\Api\V1\ApplyApiControllerProvider;
use SenseiApply\Controllers\ResumesControllerProvider;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = true;

$app->register(new TwigServiceProvider(), ['twig.path' => __DIR__ . '/../app/Views']);
$app->register(new ServiceControllerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SecurityServiceProvider());
$app->register(new SessionServiceProvider());

$app['security.firewalls'] = array(
    'secured' => array(
        'pattern' => '^/resumes',
        'form' => array('login_path' => '/login', 'check_path' => '/resumes/login_check'),
        'logout' => array('logout_path' => '/resumes/logout'),
        'users' => array(
            'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
        ),
    ),
);

$app['config'] = $app->share(function() {
    $fileParser = new FileParser();
    return $fileParser->yaml(__DIR__ . '/../config/local.yml');
});

$app['s3'] = $app->share(function($app) {
    return S3Client::factory(['region' => $app['config']['region']]);
});

$app['dynamoDb'] = $app->share(function($app) {
    return DynamoDbClient::factory(['region' => $app['config']['region']]);
});

$app['logger'] = $app->share(function($app) {
    $config = $app['config'];
    $gitterHandler = new GitterImHandler($config['gitterToken'], $config['gitterRoomId'], Logger::NOTICE);
    $chatBuffer = new BufferHandler($gitterHandler);

    $subject = 'Sensei Apply -- Resume Received';
    $from = 'no-reply@apply.energysensei.info';
    $mailHandler = new NativeMailerHandler($config['to'], $subject, $from, Logger::NOTICE);
    $mailHandler->setContentType('text/html');
    $mailHandler->setFormatter(new HtmlFormatter());
    $mailBuffer = new BufferHandler($mailHandler);

    $logger = new Logger('cascade-sensei-apply');
    $logger->pushHandler($chatBuffer);
    $logger->pushHandler($mailBuffer);

    return $logger;
});

$app->get('/', function() use ($app) {
    return $app->redirect('/resumes');
})
->bind('homepage');

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$resumesControllerProvider = new ResumesControllerProvider();
$app->register($resumesControllerProvider);
$app->mount('/', $resumesControllerProvider);

$applyApiControllerProvider = new ApplyApiControllerProvider();
$app->register($applyApiControllerProvider);
$app->mount('/api/v1/', $applyApiControllerProvider);

$app['session.storage.handler'] = null;
ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', $app['config']['elastiCacheEndpoint']);

$app->run();
