<?php

use Aws\S3\S3Client;
use Aws\Sdk;
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

$app['aws'] = $app->share(function() {
    return new Sdk(['region' => 'us-west-2', 'version' => 'latest']);
});

$app['s3'] = $app->share(function() use ($app) {
    return $app['aws']->getS3();
});

$app['dynamoDb'] = $app->share(function() use ($app) {
    return $app['aws']->getDynamoDb();
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

    /** @var S3Client $s3 */
    $s3 = $app['s3'];

    $s3->createBucket(['Bucket' => $bucket]);
    $s3->waitUntil('BucketExists', ['Bucket' => $bucket]);

//    $s3->createBucket(
//        ['Bucket' => $bucket, '@future' => true]
//    )->then(function($result) use ($s3, $bucket) {
//        var_dump($result);
//        return $s3->getWaiter('BucketExists', ['Bucket' => $bucket])->promise();
//    })->then(function($result) use ($s3, $bucket, $request) {
//        var_dump($result);
//        /** @var UploadedFile $resumeFile */
//        $resumeFile = $request->files->get('resume');
//        $s3->upload($bucket, $resumeFile->getFilename(), $resumeFile->openFile('r'));
//    });

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
