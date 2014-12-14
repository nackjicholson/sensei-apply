<?php

namespace SenseiApply\Controllers\Api\V1;

use Aws\DynamoDb\DynamoDbClient;
use Aws\S3\S3Client;
use Psr\Log\LoggerAwareTrait;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ApplyApiController
{
    use LoggerAwareTrait;

    /** @var array */
    private $config;

    /** @var DynamoDbClient */
    private $dynamoDb;

    /** @var S3Client */
    private $s3;

    /** @var UrlGenerator */
    private $urlGenerator;

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
     * @param UrlGenerator $urlGenerator
     */
    public function setUrlGenerator($urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Apply API endpoint.
     *
     * Saves resume file to S3 bucket, stores some meta data about the resume
     * to DynamoDb, and sends out a Logger::NOTICE to notify monolog handlers
     * that a resume was received.
     *
     * @param Application $app
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function apply(Application $app, Request $request)
    {
        $tableName = $this->config['resumesMetaTable'];
        $bucket = $this->config['resumesBucket'];

        $name = $request->request->get('profile[name]', null, true);

        /** @var UploadedFile $resumeFile */
        $resumeFile = $request->files->get('resume');
        $originalFilename = $resumeFile->getClientOriginalName();
        $key = $this->generateUniqueS3Key($bucket);

        $this->s3->upload($bucket, $key, fopen($resumeFile->getPathname(), 'r+'));

        $this->dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => $this->dynamoDb->formatAttributes([
                'key' => $key,
                'name' => $name,
                'visible' => true,
                'bucket' => $bucket,
                'originalFilename' => $originalFilename
            ])
        ]);

        $resumeLink = $this->urlGenerator->generate(
            'resumes.download',
            ['bucket' => $bucket, 'key' => $key],
            UrlGenerator::ABSOLUTE_URL
        );

        $this->logger->notice("New resume received from $name, view here $resumeLink");

        return $app->json(['message' => "Thank you for applying via api {$name}"]);
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
