<?php

namespace SenseiApply\Controllers\Api\V1;

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class ApplyApiControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegistersApplyApiController()
    {
        $app = new Application();

        $app->register(new ApplyApiControllerProvider(), [
            'config' => ['foo' => 'bar'],
            'dynamoDb' => $this
                ->getMockBuilder('Aws\\DynamoDb\\DynamoDbClient')
                ->disableOriginalConstructor()
                ->getMock(),
            'dynamoDb.marshaler' => $this->getMock('Aws\\DynamoDb\\Marshaler'),
            'logger' => $this->getMock('Psr\\Log\\LoggerInterface'),
            's3' => $this
                ->getMockBuilder('Aws\\S3\\S3Client')
                ->disableOriginalConstructor()
                ->getMock(),
            'url_generator' => $this
                ->getMockBuilder('Symfony\\Component\\Routing\\Generator\\UrlGenerator')
                ->disableOriginalConstructor()
                ->getMock()
        ]);

        $applyApiController = $app['api.apply.controller'];
        $this->assertInstanceOf('SenseiApply\\Controllers\\Api\\V1\\ApplyApiController', $applyApiController);
        $this->assertAttributeEquals(['foo' => 'bar'], 'config', $applyApiController);
        $this->assertAttributeInstanceOf('Aws\\DynamoDb\\DynamoDbClient', 'dynamoDb', $applyApiController);
        $this->assertAttributeInstanceOf('Aws\\DynamoDb\\Marshaler', 'marshaler', $applyApiController);
        $this->assertAttributeInstanceOf('Aws\\S3\\S3Client', 's3', $applyApiController);
        $this->assertAttributeInstanceOf('Psr\\Log\\LoggerInterface', 'logger', $applyApiController);
        $this->assertAttributeInstanceOf('Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'urlGenerator', $applyApiController);
    }

    public function testConnectsApplyApiRoutes()
    {
        $applyApiController = $this->getMock(__NAMESPACE__ . '\\ApplyApiController');
        $applyApiController->expects($this->once())->method('apply')->with();

        $app = new Application();

        $app['api.apply.controller'] = $app->share(function() use ($applyApiController) {
           return $applyApiController;
        });

        $app->register(new ServiceControllerServiceProvider());
        $app->mount('/', new ApplyApiControllerProvider());

        $request = Request::create('/apply', 'POST');
        $app->handle($request);
    }

    public function testBootIsNoop()
    {
        $sut = new ApplyApiControllerProvider();
        $sut->boot(new Application());
    }
}
