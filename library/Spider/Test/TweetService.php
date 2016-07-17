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
use ZendService\Twitter\Twitter;

class TweetService implements EventManagerAwareInterface {

    protected $twitter_config;

    public function setConfig($config) {
        $this->twitter_config = $config;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        // TODO: Implement setEventManager() method.
    }

    public function getEventManager()
    {
        // TODO: Implement getEventManager() method.
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

            $res2 = $twitter->statuses->update('Hello world!');
            if ($res2->isSuccess()) {
                throw new \Exception('send tweet failed');
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}