<?php

namespace SenseiApply\Controllers\Frontend;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

/**
 * Manages the routes and services specific to the Sensei Apply resumes app.
 */
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
            $resumesController->setS3($app['s3']);
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

        $controllers
            ->get('/resumes/{bucket}/{key}', 'resumes.controller:show')
            ->bind('resumes.download');

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
        // noop
    }
}
