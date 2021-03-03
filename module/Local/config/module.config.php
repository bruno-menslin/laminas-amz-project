<?php

namespace Local;

use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'local' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/local[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => Controller\LocalController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'local' => __DIR__ . '/../view',
        ],
    ],
];