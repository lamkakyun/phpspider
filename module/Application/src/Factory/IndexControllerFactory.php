<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/14/2016
 * Time: 5:49 PM
 */
namespace Application\Factory;

use Application\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realSL = $serviceLocator->getServiceLocator();
        $testService = $realSL->get('Application\Service\TestService');

        return new IndexController($testService);

//        $controller = new IndexController();
//        $controller->setTestService($testService);
//        return $controller;
    }
}