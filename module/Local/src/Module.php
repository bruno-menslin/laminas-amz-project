<?php

namespace Local; //Local\Module

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface {
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\LocalTable::class => function($container) {
                    return new Model\LocalTable($container->get(Model\LocalTableGateway::class));
                },
                        
                Model\LocalTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Local());
                    return new TableGateway('local', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }
    
    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\LocalController::class => function($container) {
                    return new Controller\LocalController($container->get(Model\LocalTable::class));
                },
            ],
        ];
    }
}
