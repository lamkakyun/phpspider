<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/14/2016
 * Time: 5:31 PM
 */
namespace Application\Service;

use PHPHtmlParser\Dom;
use Spider\Test\Example;
use Spider\Version;
use Zend\Config\Config;
use Zend\EventManager\EventManager;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

/**
 * Class TestService
 * @package Application\Service
 * @desc 测试服务
 */
class TestService {

    protected static $config;

    public function __construct() { }

    public function setConfig(Config $config) {
        self::$config = $config->toArray();
    }

    public function getConfig() {
        return self::$config;
    }

    /**
     * test EventManager 1
     */
    public function test0() {
        $events = new EventManager();
        $events->attach('do', function($e) {
            $event = $e->getName();
            $params = $e->getParams(); // 这里没有参数哦
            printf('handle event %s, with parameters %s', $event, json_encode($params));
        });

        $events->trigger('do');
    }

    /**
     * 创建自动加载命名空间，并使用其中的类
     * @desc 在Module 类的 getAutoload 方法中设置
     */
    public function test01() {
//        echo Version::getCurrent();
        echo Version::getLatest();
    }

    public function test02() {
        $example = new Example();
        $example->getEventManager()->attach('dosth', function($e) {
            $event = $e->getName();
            $target = get_class($e->getTarget());
            $params = $e->getParams();
            printf(
                'Handled event "%s" on target "%s", with parameters %s',
                $event,
                $target,
                json_encode($params)
            );
        });
        $example->dosth('bar', 'bat'); // 调用方法，触发事件
    }


    /**
     * 测试发送邮件
     */
    public function test03() {
        $gmail_config = self::$config['gmail'];
        try {
            $message = new Message();
            $message->setBody('This ia the text of email.');
            $message->setFrom('lamkakyun@spider.com', 'lamkakyun');
            $message->addTo('756431672@qq.com', 'jet');
            $message->setSubject('TestSubject');

            $smtp_options = new SmtpOptions();
            $smtp_options->setHost('smtp.gmail.com')
                         ->setConnectionClass('login')
                         ->setName('smtp.gmail.com')
                         ->setConnectionConfig($gmail_config);

            $transport = new Smtp($smtp_options);
            $transport->send($message);

            echo 'bingo';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

    public function test1() {
        $url = "http://log.lamkakyun.com";
        $request = \Requests::get($url);
        $dom = new Dom();
        $dom->load($request->body);
//        echo $request->body;
//        echo $dom->find('title')[0];

        $collection = $dom->find('.post-title');
        $iterator = $collection->getIterator();
        echo "Iterating over: " . $iterator->count() . " values\n";

        while( $iterator->valid() )
        {
//            echo $iterator->key() . "=" . $iterator->current() . "\n";
//            echo $iterator->key() . "=" . $iterator->current()->innerHtml . "\n";
            echo $iterator->key() . "=" . $iterator->current()->innerHtml . "\n";
            $iterator->next();
        }
    }


    /**
     * 抓取知乎的一个网页，并进行分析
     */
    public function test2() {
        $url = "https://www.zhihu.com/question/25365330";

        $request = \Requests::get($url);

        if (preg_match('#<title>([\s\S]*?)</title>#i',$request->body, $matchs)) {
            $title = trim($matchs[1]);
        } else {
            $title = '[no title found]';
        }

        echo "标题是：" . $title . "\n";

        $dom = new Dom();
        $dom->load($request->body);

        $collection = $dom->find('.zm-item-answer');
        $iterator = $collection->getIterator();
        echo '总共找到' . $iterator->count() . "个回答\n\n";

        while($iterator->valid()) {
            $answer = $iterator->current();

            $dom->load($answer);
            echo $dom->find('.author-link')[0]->text . "\n";
            echo $dom->find('.zm-editable-content')[0]->text;
            echo "\n=============================================\n\n";

            $iterator->next();
        }
    }


    /**
     * 尝试进行 登陆操作
     */
    public function test3() {

        $url = "http://log.lamkakyun.com/admin/login.php?referer=http%3A%2F%2Flog.lamkakyun.com%2Fadmin%2F";
        // 使用 Zend_Config 读取配置会不会好一点
        $username = self::$config['website']['loglamkakyuncom']['username'];
        $password = self::$config['website']['loglamkakyuncom']['password'];


//        \Requests::request()
    }
}