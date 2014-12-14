<?php

namespace SenseiApply\Controllers\Api\V1;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

function uniqid()
{
    $callCount = ApplyApiControllerTest::$uniqidCallNumber++;
    return ($callCount > 0) ? ApplyApiControllerTest::NEW_UNIQUE_KEY : ApplyApiControllerTest::EXISTING_KEY;
}

class ApplyApiControllerTest extends \PHPUnit_Framework_TestCase
{
    const NEW_UNIQUE_KEY = 'myKey';
    const EXISTING_KEY = 'usedKey';

    public static $uniqidCallNumber = 0;

    /** @var ApplyApiController */
    private $sut;

    /** @var array */
    private static $config = [
        'resumesBucket' => 'test.bucket',
        'resumesMetaTable' => 'test.table',
        'region' => 'test.region'
    ];

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dynamoDb;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $marshaler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $s3;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $urlGenerator;

    public function setUp()
    {
        $this->constructMocks();

        $this->sut = new ApplyApiController();
        $this->sut->setConfig(static::$config);
        $this->sut->setDynamoDb($this->dynamoDb);
        $this->sut->setMarshaler($this->marshaler);
        $this->sut->setLogger($this->logger);
        $this->sut->setS3($this->s3);
        $this->sut->setUrlGenerator($this->urlGenerator);
    }

    public function testApply()
    {
        $pathname = __DIR__ . '/Fixtures/test.gif';

        $resumeClientOriginalName = 'test.filename';
        $resumeFile = new UploadedFile(
            $pathname,
            $resumeClientOriginalName,
            null,
            filesize($pathname),
            UPLOAD_ERR_OK,
            true
        );

        $name = 'test.name';

        /** @var Request $request */
        $request = $this->getMock('Symfony\\Component\\HttpFoundation\\Request');
        $request->request = $this->getMock('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $request->files = $this->getMock('Symfony\\Component\\HttpFoundation\\FileBag');

        $request->files->expects($this->once())->method('get')->with('resume')->willReturn($resumeFile);
        $request->request->expects($this->once())->method('get')->with('profile[name]')->willReturn($name);

        $this->s3
            ->expects($this->exactly(2))
            ->method('doesObjectExist')
            ->withConsecutive(['test.bucket', self::EXISTING_KEY], ['test.bucket', self::NEW_UNIQUE_KEY])
            ->willReturnOnConsecutiveCalls(true, false);

        $this->s3->expects($this->once())->method('upload')->with('test.bucket', self::NEW_UNIQUE_KEY);

        $this->marshaler
            ->expects($this->once())
            ->method('marshalItem')
            ->with([
                'key' => self::NEW_UNIQUE_KEY,
                'name' => 'test.name',
                'visible' => true,
                'bucket' => 'test.bucket',
                'originalFilename' => 'test.filename'
            ])
            ->willReturn(['foo' => 'bar']);

        $this->dynamoDb
            ->expects($this->once())
            ->method('putItem')
            ->with([
                'TableName' => 'test.table',
                'Item' => ['foo' => 'bar']
            ]);

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                'resumes.download',
                ['bucket' => 'test.bucket', 'key' => self::NEW_UNIQUE_KEY],
                UrlGenerator::ABSOLUTE_URL
            )
            ->willReturn('test.link');

        $this->logger
            ->expects($this->once())
            ->method('notice')
            ->with("New resume received from test.name, view here test.link");

        $response = new JsonResponse();
        $app = $this
            ->getMockBuilder('Silex\\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $app
            ->expects($this->once())
            ->method('json')
            ->with(['message' => 'Thank you for applying via api test.name'])
            ->willReturn($response);

        $this->assertSame($response, $this->sut->apply($app, $request));
    }

    private function constructMocks()
    {
        $this->dynamoDb = $this
            ->getMockBuilder('Aws\\DynamoDb\\DynamoDbClient')
            ->setMethods(['putItem'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMock('Psr\\Log\\LoggerInterface');

        $this->marshaler = $this->getMock('Aws\\DynamoDb\\Marshaler');

        $this->s3 = $this
            ->getMockBuilder('Aws\\S3\\S3Client')
            ->setMethods(['doesObjectExist', 'upload'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlGenerator = $this
            ->getMockBuilder('Symfony\\Component\\Routing\\Generator\\UrlGenerator')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
