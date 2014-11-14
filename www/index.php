<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\ResourceNotFoundException;
use Aws\S3\S3Client;
use Monolog\Logger;
use Nack\FileParser\FileParser;
use Nack\Monolog\Handler\GitterImHandler;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = true;

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

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

$app->get('/', function() use ($app) {

    $bucket = $app['config']['resumesBucket'];

    /** @var S3Client $s3 */
    $s3 = $app['s3'];

    $iterator = $s3->getIterator('ListObjects', array(
        'Bucket' => $bucket
    ));

    $fileList = [];
    foreach ($iterator as $object) {
        $fileList[] =  $object['Key'] . "\n";
    }

    return $app['twig']->render('home.twig', array(
        'fileList' => $fileList,
    ));

});

$app->post('/api/v1/apply', function(Request $request) use ($app) {
    $bucket = $app['config']['resumesBucket'];
    $tableName = $app['config']['applicantsTable'];
    $region = $app['config']['region'];

    /** @var Logger $logger */
    $logger = $app['logger'];

    /** @var S3Client $s3 */
    $s3 = $app['s3'];

    /** @var DynamoDbClient $dynamoDb */
    $dynamoDb = $app['dynamoDb'];

    if (!$s3->doesBucketExist($bucket)) {
        $s3->createBucket(['Bucket' => $bucket, 'LocationConstraint' => $region]);
        $s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
    }

    try {
        $dynamoDb->describeTable(['TableName' => $tableName]);
    } catch (ResourceNotFoundException $resourceNotFoundException) {
        $dynamoDb->createTable([
            'TableName' => $tableName,
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'name',
                    'AttributeType' => 'S'
                ]
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'name',
                    'KeyType'       => 'HASH'
                ]
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits'  => 1,
                'WriteCapacityUnits' => 1
            ]
        ]);
    }

    /** @var UploadedFile $resumeFile */
    $resumeFile = $request->files->get('resume');
    $key = $resumeFile->getClientOriginalName();
    $s3->upload($bucket, $key, fopen($resumeFile->getPathname(), 'r+'));

    $profile = $request->request->get('profile');
    $name = $profile['name'];

    $dynamoDb->putItem([
        'TableName' => $tableName,
        'Item' => $dynamoDb->formatAttributes([
            'name' => $name,
            'visible' => true,
            'bucket' =>  $bucket,
            'key' => $key
        ])
    ]);

    $link = $app['config']['homeLink'];
    $logger->notice("New resume received for $name, check $link");

    return $app->json("Thank you for applying via api {$name}!");
});

$app->run();
