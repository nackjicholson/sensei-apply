<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\S3\S3Client;
use Monolog\Logger;
use Nack\FileParser\FileParser;
use Nack\Monolog\Handler\GitterImHandler;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = true;

$app['config'] = $app->share(function() {
    $fileParser = new FileParser();
    return $fileParser->yaml(__DIR__ . '/../config/local.yml');
});

$app['s3'] = $app->share(function() use ($app) {
    return S3Client::factory(['region' => $app['config']['region']]);
});

$app['dynamoDb'] = $app->share(function() use ($app) {
    return DynamoDbClient::factory(['region' => $app['config']['region']]);
});

$app['logger'] = $app->share(function() use ($app) {
    $config = $app['config'];
    $gitterHandler = new GitterImHandler($config['gitterToken'], $config['gitterRoomId'], Logger::NOTICE);
    $bufferHandler = new \Monolog\Handler\BufferHandler($gitterHandler);

    $logger = new Logger('cascade-sensei-apply');
    $logger->pushHandler($bufferHandler);

    return $logger;
});

$app->post('/api/v1/apply', function(Request $request) use ($app) {
    $bucket = $app['config']['resumesBucket'];
    $region = $app['config']['region'];

    /** @var S3Client $s3 */
    $s3 = $app['s3'];

    if (!$s3->doesBucketExist($bucket)) {
        $s3->createBucket(['Bucket' => $bucket, 'LocationConstraint' => $region]);
        $s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
    }

    /** @var UploadedFile $resumeFile */
    $resumeFile = $request->files->get('resume');

    $s3->upload($bucket, $resumeFile->getClientOriginalName(), $resumeFile->openFile('r'));

    $dynamoDb = $app['dynamoDb'];

//    $dynamoDb->createBucket(
//        ['Bucket' => $bucket, '@future' => true]
//    )

//    $profile = $request->request->get('profile');

//    var_dump($request->request);
//    var_dump($request->files);

    return $app->json(true);
});

$app->run();
