<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-17
 * Time: 下午2:30
 */

namespace Spider\Test;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class LogService implements EventManagerAwareInterface
{

    protected $eventManager;

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers(array(
            get_called_class()
        ));
        $this->eventManager = $eventManager;
    }

    public function getEventManager()
    {
        if (null == $this->eventManager) {
//            $this->eventManager = new EventManager();
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }


    public function logConsole()
    {
        $writer = new Stream('php://output');
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info('console information message log!');

        $this->getEventManager()->trigger('logConsole', null, array('content' => 'this is a test!'));
    }

    public function logFile($file = null)
    {
        if (!isset($file) or empty($file)) {
            $file = '/tmp/logs/phpspider.log';
        }
        if (!file_exists($file)) {
            $file_obj = new \SplFileInfo($file);
            if (!file_exists($file_obj->getPath())) mkdir($file_obj->getPath());
            touch($file);
        }
        $writer = new Stream($file);
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info('file information message log!');
    }
}