<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-8-27
 * Time: 下午2:25
 */

namespace Admin;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface, BootstrapListenerInterface
{

    public function onBootstrap(EventInterface $e)
    {
        // TODO: Implement onBootstrap() method.

        // 调用初始化session方法
        $this->initSession([
            'remember_me_seconds' => 180,
            'use_cookies'         => true,
            'cookie_httponly'     => true,
        ]);
    }


    /**
     * 自定义方法，初始化session
     */
    public function initSession($config)
    {

        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config);
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
    }


    public function getConfig()
    {
        // TODO: Implement getConfig() method.
        return include __DIR__ . '/config/module.config.php';
    }


    public function getAutoloaderConfig()
    {
        // TODO: Implement getAutoloaderConfig() method.
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Spider'      => getcwd() . '/library/Spider',
                ),
            ),
        );
    }
}