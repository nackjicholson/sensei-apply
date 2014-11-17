<?php

namespace SenseiApply\Controllers;

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class ResumesControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegistersResumesController()
    {
        $app = new Application();

        $app->register(new ResumesControllerProvider(), [
            'config' => ['resumesMetaTable' => 'test.table'],
            'dynamoDb' => $this
                ->getMockBuilder('Aws\\DynamoDb\\DynamoDbClient')
                ->disableOriginalConstructor()
                ->getMock(),
            's3' => $this
                ->getMockBuilder('Aws\\S3\\S3Client')
                ->disableOriginalConstructor()
                ->getMock(),
            'twig' => $this->getMock('\\Twig_Environment')
        ]);

        $resumesController = $app['resumes.controller'];

        $this->assertInstanceOf('SenseiApply\\Controllers\\ResumesController', $resumesController);
        $this->assertAttributeInstanceOf('Aws\\DynamoDb\\DynamoDbClient', 'dynamoDb', $resumesController);
        $this->assertAttributeInstanceOf('Aws\\S3\\S3Client', 's3', $resumesController);
        $this->assertAttributeEquals('test.table', 'tableName', $resumesController);
        $this->assertAttributeInstanceOf('\\Twig_Environment', 'twig', $resumesController);
    }

    public function testConnectsResumesRoutes()
    {
        $resumesController = $this->getMock(__NAMESPACE__ . '\\ResumesController');
        $resumesController->expects($this->once())->method('index')->with();

        $app = new Application();

        $app['resumes.controller'] = $app->share(function() use ($resumesController) {
            return $resumesController;
        });

        $app->register(new ServiceControllerServiceProvider());
        $app->mount('/', new ResumesControllerProvider());

        $request = Request::create('/resumes');
        $app->handle($request);
    }

    public function testBootIsNoop()
    {
        $sut = new ResumesControllerProvider();
        $sut->boot(new Application());
    }
}
