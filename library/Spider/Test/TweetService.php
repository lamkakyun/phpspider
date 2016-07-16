<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-16
 * Time: 下午2:05
 */
namespace Spider\Test;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class TweetService implements EventManagerAwareInterface {

    public function setEventManager(EventManagerInterface $eventManager)
    {
        // TODO: Implement setEventManager() method.
    }

    public function getEventManager()
    {
        // TODO: Implement getEventManager() method.
    }

}