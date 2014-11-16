<?php

namespace SenseiApply\Controllers\Api\V1;

use Aws\S3\S3Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumesApiController
{
    /** @var S3Client */
    private $s3;

    /**
     * @param S3Client $s3
     */
    public function setS3($s3)
    {
        $this->s3 = $s3;
    }

    /**
     * @param Application $app
     * @param string $bucket
     * @param string $key
     *
     * @return JsonResponse|StreamedResponse
     */
    public function download(Application $app, $bucket, $key)
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
