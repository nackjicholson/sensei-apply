<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\S3\S3Client;
use Monolog\Logger;
use Nack\FileParser\FileParser;
use Nack\Monolog\Handler\GitterImHandler;
use SenseiApply\Controllers\Api\V1\ApplyApiControllerProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = true;

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->register(new ServiceControllerServiceProvider());

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
    $bufferHandler = new \Monolog\Handler\BufferHandler($gitterHandler);

    $logger = new Logger('cascade-sensei-apply');
    $logger->pushHandler($bufferHandler);

    return $logger;
});

$app->get('/', function() use ($app) {
    return $app->redirect('/resumes');
});

$resumesControllerProvider = new \SenseiApply\Controllers\ResumesControllerProvider();
$app->register($resumesControllerProvider);
$app->mount('/', $resumesControllerProvider);

$applyApiControllerProvider = new ApplyApiControllerProvider();
$app->register($applyApiControllerProvider);
$app->mount('/api/v1/', $applyApiControllerProvider);

$app->run();
