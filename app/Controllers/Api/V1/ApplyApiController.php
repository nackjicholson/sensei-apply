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

        $this->ensureBucketExists($bucket, $region);
        $this->ensureTableExists($tableName);

        $name = $request->request->get('profile[name]', null, true);

        /** @var UploadedFile $resumeFile */
        $resumeFile = $request->files->get('resume');
        $originalFilename = $resumeFile->getClientOriginalName();
        $key = $this->generateUniqueS3Key($bucket);
        $this->s3->upload($bucket, $key, fopen($resumeFile->getPathname(), 'r+'));

        $this->dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => $this->dynamoDb->formatAttributes([
                'name' => $name,
                'visible' => true,
                'bucket' => $bucket,
                'key' => $key,
                'originalFilename' => $originalFilename
            ])
        ]);

        $this->logger->notice("New resume received from $name, check $link");
        return $app->json("Thank you for applying via api {$name}");
    }

    /**
     * @param $bucket
     * @param $region
     */
    private function ensureBucketExists($bucket, $region)
    {
        if (!$this->s3->doesBucketExist($bucket)) {
            $this->s3->createBucket(['Bucket' => $bucket, 'LocationConstraint' => $region]);
            $this->s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
        }
    }

    /**
     * @param $tableName
     */
    private function ensureTableExists($tableName)
    {
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
    }

    /**
     * Generates a unique key for the provide bucket name.
     * Checks for the existence of the key, and retries if it already exists.
     *
     * @param string $bucket
     * @return string
     */
    private function generateUniqueS3Key($bucket)
    {
        $key = uniqid();

        while ($this->s3->doesObjectExist($bucket, $key)) {
            $key = uniqid();
        }

        return $key;
    }
}
