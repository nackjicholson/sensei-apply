<?php

namespace SenseiApply\Controllers\Api\V1;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

class ResumesApiControllerProvider implements ControllerProviderInterface, ServiceProviderInterface
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
        $app['api.resumes.controller'] = $app->share(function($app) {
            $resumesApiController = new ResumesApiController();
            $resumesApiController->setS3($app['s3']);
            return $resumesApiController;
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

        $controllers->get('/resumes/download/{bucket}/{key}', 'api.resumes.controller:download');

        return $controllers;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // noop
    }
}
