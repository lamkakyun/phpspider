<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-18
 * Time: 下午9:42
 */

namespace Spider\Test;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SendListener implements ListenerAggregateInterface {

    protected $listeners = array();

    public function attach(EventManagerInterface $events)
    {
        $shareEvents = $events->getSharedManager();
        $this->listeners[] = $shareEvents->attach('Spider\Test\LogService', 'logConsole', array($this, 'onLogConsole'), 100);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    public function onLogConsole($e) {
        echo "----------------------------\n";
        echo "| this is a listener class |\n";
        echo "----------------------------\n";
        var_dump($e);
    }


}