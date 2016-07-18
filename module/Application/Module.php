<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Spider\Test\SendListener;
use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface, ConsoleUsageProviderInterface, ConsoleBannerProviderInterface/*, BootstrapListenerInterface*/
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $shareManager = $eventManager->getSharedManager();
        $shareManager->attach('Spider\Test\LogService', 'logConsole', function($e) {
            echo "------------------------------\n";
            echo "| this is a aynmous listener |\n";
            echo "------------------------------\n";
            var_dump($e);
        });

        $eventManager->attach(new SendListener());
    }
//    public function onBootstrap(MvcEvent $e)
//    {
//        $eventManager = $e->getApplication()->getEventManager();
////         $eventManager->attach('sendTweet', function($e) {
////             var_dump($e);
////         });
//
////        $sharedEventManager = $eventManager->getSharedManager();
////        $sharedEventManager->attach('Spider\Test\TweetService', 'sendTweet', function($e) {
////            var_dump($e);
////        }, 100);
//
////        $sharedEventManager->attach('Application\Service\ServiceInterface', 'sendTweet', function($e) {
////            var_dump($e);
////        }, 100);
//    }


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
                    'Spider'      => getcwd() . '/library/Spider',
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
