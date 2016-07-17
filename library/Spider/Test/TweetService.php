<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-16
 * Time: 下午2:05
 */
namespace Spider\Test;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use ZendService\Twitter\Twitter;

class TweetService implements EventManagerAwareInterface {

    protected $twitter_config;

    protected $eventManager;

    public function setConfig($config) {
        $this->twitter_config = $config;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        // TODO: Implement setEventManager() method.
        $this->eventManager = $eventManager;
    }

    public function getEventManager()
    {
        // TODO: Implement getEventManager() method.
        if (null == $this->eventManager) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
    }


    public function sendTweet($content)
    {
        try {
            if (!isset($this->twitter_config) or empty($this->twitter_config)) {
                throw new \Exception('no twitter config');
            }

            $twitter = new Twitter($this->twitter_config);

            $res = $twitter->accountVerifyCredentials();
            if (!$res->isSuccess()) {
                die('Something is wrong with my credentials!');
            }

            $res2 = $twitter->statuses->update($content);
            if ($res2->isSuccess()) {
                throw new \Exception('send tweet failed');
            } else {
                echo 'Bingo!';
            }

            // 触发sendTweet事件（用来发送邮件，记录日志等等操作）
            $this->eventManager->trigger('sendTweet', null, array('content', $content));

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}