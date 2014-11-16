<?php

namespace SenseiApply\Controllers;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

class ResumesControllerProvider implements ControllerProviderInterface, ServiceProviderInterface
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
        $app['resumes.controller'] = $app->share(function($app) {
            $resumesController = new ResumesController();
            $resumesController->setDynamoDb($app['dynamoDb']);
            $resumesController->setTableName($app['config']['resumesMetaTable']);
            $resumesController->setTwig($app['twig']);
            return $resumesController;
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

        $controllers->get('/resumes', 'resumes.controller:index');

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
        // TODO: Implement boot() method.
    }
}
