<?php

namespace SenseiApply\Controllers;

use Aws\DynamoDb\Iterator\ItemIterator;

class ResumesControllerTest extends \PHPUnit_Framework_TestCase
{
    const TABLE_NAME = 'test.table';

    /** @var ResumesController */
    private $sut;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dynamoDb;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $twig;

    public function setUp()
    {
        $this->constructMocks();
        $this->sut = new ResumesController();
        $this->sut->setDynamoDb($this->dynamoDb);
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

    private function constructMocks()
    {
        $this->dynamoDb = $this
            ->getMockBuilder('Aws\\DynamoDb\\DynamoDbClient')
            ->setMethods(['getIterator'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMock('\\Twig_Environment');
    }
}
