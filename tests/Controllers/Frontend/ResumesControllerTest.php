<?php

namespace SenseiApply\Controllers\Frontend;

use Aws\DynamoDb\Iterator\ItemIterator;
use Symfony\Component\HttpFoundation\Response;

class ResumesControllerTest extends \PHPUnit_Framework_TestCase
{
    const TABLE_NAME = 'test.table';

    /** @var ResumesController */
    private $sut;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dynamoDb;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $s3;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $twig;

    public function setUp()
    {
        $this->constructMocks();
        $this->sut = new ResumesController();
        $this->sut->setDynamoDb($this->dynamoDb);
        $this->sut->setS3($this->s3);
        $this->sut->setTableName(self::TABLE_NAME);
        $this->sut->setTwig($this->twig);
    }

    public function testIndex()
    {
        $scanIterator = $this->getMock('Guzzle\\Service\\Resource\\ResourceIteratorInterface');

        $this->dynamoDb
            ->expects($this->once())
            ->method('getIterator')
            ->with('Scan', ['TableName' => self::TABLE_NAME])
            ->willReturn($scanIterator);

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('resumes/index.twig', [
                'itemList' => new ItemIterator($scanIterator)
            ])
            ->willReturn('html.string');

        $this->assertEquals('html.string', $this->sut->index());
    }

    public function testShowStreamsS3File()
    {
        $app = $this
            ->getMockBuilder('Silex\\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $app
            ->expects($this->once())
            ->method('stream')
            ->with($this->isType('callable'), Response::HTTP_OK, ['Content-Type' => 'application/pdf'])
            ->willReturn('streaming.response');

        $this->assertEquals('streaming.response', $this->sut->show($app, '', ''));
    }

    public function testShowReturnsJson500OnError()
    {
        $app = $this
            ->getMockBuilder('Silex\\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $app
            ->expects($this->once())
            ->method('stream')
            ->with($this->isType('callable'), Response::HTTP_OK, ['Content-Type' => 'application/pdf'])
            ->willThrowException(new \Exception('exception message'));

        $app
            ->expects($this->once())
            ->method('json')
            ->with('exception message', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->willReturn('json.ise.response');

        $this->assertEquals('json.ise.response', $this->sut->show($app, '', ''));
    }

    private function constructMocks()
    {
        $this->dynamoDb = $this
            ->getMockBuilder('Aws\\DynamoDb\\DynamoDbClient')
            ->setMethods(['getIterator'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->s3 = $this
            ->getMockBuilder('Aws\\S3\\S3Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMock('\\Twig_Environment');
    }
}
