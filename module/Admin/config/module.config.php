<?php

return [
    'router'      => [
        'routes' => [

            // 定义路由
            'admin' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/admin', // 这里需要注意大小写
                    'defaults' => [
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'default' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_]*',
                            ],
                            'defaults'    => [],
                        ],
                    ],
                ],
            ],
        ],
    ],


    'controllers' => [
        'invokables' => [
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Public' => 'Admin\Controller\PublicController',
        ],
    ],


    'view_manager'    => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => array(
            'layout/admin'           => __DIR__ . '/../view/layout/layout.phtml',
            'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack'      => array(
            __DIR__ . '/../view',
        ),
    ),

];