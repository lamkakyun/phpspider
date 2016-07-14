<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/14/2016
 * Time: 5:30 PM
 */
namespace Application\Factory;

use Application\Service\TestService;
use Zend\Config\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TestServiceFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $service = new TestService($config);
        $service->setConfig(new Config($config));
        return $service;
    }

}