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

class TweetService implements EventManagerAwareInterface
{

    protected $twitter_config;

    protected $eventManager;

    public function setConfig($config)
    {
        $this->twitter_config = $config;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        // TODO: Implement setEventManager() method.
        $eventManager->addIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->eventManager = $eventManager;
    }

    public function getEventManager()
    {
        // TODO: Implement getEventManager() method.
        if (null == $this->eventManager) {
//            $this->eventManager = new EventManager(); // 通过setEventManager才是正确的，这是错误的语句
            $this->setEventManager(new EventManager());
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

            // 添加事件监听，在这里添加，是因为在Module-onBootstrap 中添加失败，没有效果
            $this->getEventManager()->attach('sendTweet', function ($e) {
                var_dump($e);
            });
//            // 触发sendTweet事件（用来发送邮件，记录日志等等操作）
            $this->getEventManager()->trigger('sendTweet', null, array('content', $content));

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
