<?php

namespace app\frontend;

use Phalcon\Loader;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;

class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders(
        DiInterface $container = null
    )
    {
        $loader = new Loader();
        $loader->registerNamespaces(
            [
                'app\frontend\Controllers' => APP_PATH . '/frontend/controllers',
                'app\frontend\Models'      => '../frontend/models',
            ]
        );

        $loader->register();
    }

    public function registerServices(DiInterface $container)
    {
        // Registering a dispatcher
        $container->set(
            'dispatcher',
            function () {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(
                    'app\frontend\Controllers'
                );

                return $dispatcher;
            }
        );

        // Registering the view component
        $container->set(
            'view',
            function () {
                $view = new View();
                $view->setViewsDir(
                    APP_PATH.'/frontend/views'
                );

                return $view;
            }
        );
    }
}