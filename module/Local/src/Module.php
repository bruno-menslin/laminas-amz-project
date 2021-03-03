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
                    
                    $localTableGateway = $container->get(Model\LocalTableGateway::class);
                    $typeTableGateway = $container->get(Model\TypeTableGateway::class);                   
            
                    return new Model\LocalTable($localTableGateway, $typeTableGateway);
                },
                        
                Model\LocalTableGateway::class => function($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Local());
                    return new TableGateway('local', $dbAdapter, null, $resultSetPrototype);
                },
                        
                Model\TypeTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Type()); // entity object Type

                    return new TableGateway('local_type', $dbAdapter, null, $resultSetPrototype);
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
