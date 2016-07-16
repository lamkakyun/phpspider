<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-16
 * Time: 下午1:38
 */
namespace Spider\Test;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;


/**
 * Class Example
 * @package Spider\Test
 * @desc 参考官网写的一个事件管理
 */
class Example implements EventManagerAwareInterface
{

    protected $events;

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $this->events = $eventManager;
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    public function dosth($foo, $baz)
    {
        $params = compact('foo', 'baz');
        $this->getEventManager()->trigger(__FUNCTION__, $this, $params);
    }

}
