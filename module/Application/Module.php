<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface, ConsoleUsageProviderInterface, ConsoleBannerProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleBanner(AdapterInterface $console)
    {
//        return
//            "==------------------------------------------------------==\n" .
//            "        Welcome to my ZF2 Console-enabled app             \n" .
//            "==------------------------------------------------------==\n" .
//            "Version 0.0.1\n"
//            ;

        return "";
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return '';
//        return array(
//            // Describe available commands
//            'user resetpassword [--verbose|-v] EMAIL' => 'Reset password for a user',
//
//            // Describe expected parameters
//            array('EMAIL', 'Email of the user for a password reset'),
//            array('--verbose|-v', '(optional) turn on verbose mode'),
//        );
    }


}
