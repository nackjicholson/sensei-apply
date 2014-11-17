<?php

namespace SenseiApply\Controllers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Iterator\ItemIterator;
use Aws\S3\S3Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumesController
{
    /** @var DynamoDbClient */
    private $dynamoDb;

    /** @var S3Client */
    private $s3;

    /** @var string */
    private $tableName;

    /** @var \Twig_Environment */
    private $twig;

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
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param \Twig_Environment $twig
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;
    }

    public function index()
    {
        return $this->twig->render('resumes/index.twig', [
            'itemList' => new ItemIterator($this->dynamoDb->getIterator('Scan', [
                'TableName' => $this->tableName
            ]))
        ]);
    }

    /**
     * @param Application $app
     * @param string $bucket
     * @param string $key
     *
     * @return JsonResponse|StreamedResponse
     */
    public function show(Application $app, $bucket, $key)
    {
        try {
            $stream = function() use ($bucket, $key) {
                $this->s3->registerStreamWrapper();
                readfile("s3://$bucket/$key");
            };

            return $app->stream($stream, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
        } catch (\Exception $exception) {
            return $app->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
