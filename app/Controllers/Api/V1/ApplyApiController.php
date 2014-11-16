<?php

namespace SenseiApply\Controllers\Api\V1;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\ResourceNotFoundException;
use Aws\S3\S3Client;
use Psr\Log\LoggerAwareTrait;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApplyApiController
{
    use LoggerAwareTrait;

    /** @var array */
    private $config;

    /** @var DynamoDbClient */
    private $dynamoDb;

    /** @var S3Client */
    private $s3;

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param DynamoDbClient $dynamoDb
     */
    public function setDynamoDb($dynamoDb)
    {
        $this->dynamoDb = $dynamoDb;
    }

    /**
     * @param S3Client $s3
     */
    public function setS3($s3)
    {
        $this->s3 = $s3;
    }

    /**
     * @param Application $app
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Application $app, Request $request)
    {
        $tableName = $this->config['resumesMetaTable'];
        $bucket = $this->config['resumesBucket'];
        $region = $this->config['region'];
        $link = $this->config['homeLink'];

        if (!$this->s3->doesBucketExist($bucket)) {
            $this->s3->createBucket(['Bucket' => $bucket, 'LocationConstraint' => $region]);
            $this->s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
        }

        try {
            $this->dynamoDb->describeTable(['TableName' => $tableName]);
        } catch (ResourceNotFoundException $resourceNotFoundException) {
            $this->dynamoDb->createTable([
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
                        'KeyType' => 'HASH'
                    ]
                ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 1,
                    'WriteCapacityUnits' => 1
                ]
            ]);
        }

        $name = $request->request->get('profile[name]', null, true);

        /** @var UploadedFile $resumeFile */
        $resumeFile = $request->files->get('resume');
        $key = $resumeFile->getClientOriginalName();
        $this->s3->upload($bucket, $key, fopen($resumeFile->getPathname(), 'r+'));

        $this->dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => $this->dynamoDb->formatAttributes([
                'name' => $name,
                'visible' => true,
                'bucket' => $bucket,
                'key' => $key
            ])
        ]);

        $this->logger->notice("New resume received from $name, check $link");
        return $app->json("Thank you for applying via api {$name}");
    }
}
