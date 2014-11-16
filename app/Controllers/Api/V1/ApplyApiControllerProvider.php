<?php

namespace SenseiApply\Controllers\Api\V1;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

/**
 * Manages the routes and services specific to the Apply API.
 */
class ApplyApiControllerProvider implements ControllerProviderInterface, ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['api.apply.controller'] = $app->share(function($app) {
            $applyApiController = new ApplyApiController();
            $applyApiController->setConfig($app['config']);
            $applyApiController->setDynamoDb($app['dynamoDb']);
            $applyApiController->setLogger($app['logger']);
            $applyApiController->setS3($app['s3']);
            return $applyApiController;
        });
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->post('/apply', 'api.apply.controller:store');

        return $controllers;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
