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
use Spider\Test\LogService;
use Spider\Test\MyThread;
use Spider\Test\PublicService;
use Spider\Test\TweetService;
use Spider\Version;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Zend\Config\Config;
use Zend\Crypt\Password\Bcrypt;
use Zend\EventManager\EventManager;
use Zend\Http\Client;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

/**
 * Class TestService
 * @package Application\Service
 * @desc    测试服务
 */
class TestService
{

    protected static $config;

    public function __construct()
    {
    }

    public function setConfig(Config $config)
    {
        self::$config = $config->toArray();
    }

    public function getConfig()
    {
        return self::$config;
    }

    /**
     * test EventManager 1
     */
    public function test0()
    {
        $events = new EventManager();
        $events->attach('do', function ($e) {
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
    public function test01()
    {
//        echo Version::getCurrent();
        echo Version::getLatest();
    }

    public function test02()
    {
        $example = new Example();
        $example->getEventManager()->attach('dosth', function ($e) {
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
    public function test03()
    {
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
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * 测试 console类 的可选参数
     * 测试事件监听器
     * @param $config
     */
    public function test04($config)
    {
        $logService = new LogService();
        $name = $config['name'];

        if ($name == 'file') {
            $logService->logFile();
        } elseif ($name == 'console') {
            $logService->logConsole();
        } else {
            echo 'do nothing!';
        }
    }

    /**
     * Zend\Crypt 使用了https://github.com/ircmaxell/PHP-CryptLib 里面的库！
     */
    public function test05()
    {
        // MD5, SHA1, SHA256, SHA512, SHA-3,等Hash算法。这些算法都是不可逆的
        // bcrypt 也是不可以逆的算法，创建加密的速度慢，所以穷举破解的速度慢，而前面的快
        $bcrypt = new Bcrypt();
        $bpass = $bcrypt->create('123456');
        var_dump($bpass);
        var_dump($bcrypt->verify('123456', $bpass));
    }


    /**
     * 测试twitter service 创建tweet，并测试事件管理器的使用
     */
    public function test06($params)
    {
        if (!isset($params['content']) or empty($params['content'])) {
            die('please enter your content!');
        }

        $config = $this->getConfig();
        $tweet = new TweetService();
        $tweet->setConfig($config['twitter']);
        $content = $params['content'];
        $tweet->sendTweet($content);
    }

    public function test1()
    {
        $url = "http://log.lamkakyun.com";
        $request = \Requests::get($url);
        $dom = new Dom();
        $dom->load($request->body);
//        echo $request->body;
//        echo $dom->find('title')[0];

        $collection = $dom->find('.post-title');
        $iterator = $collection->getIterator();
        echo "Iterating over: " . $iterator->count() . " values\n";

        while ($iterator->valid()) {
//            echo $iterator->key() . "=" . $iterator->current() . "\n";
//            echo $iterator->key() . "=" . $iterator->current()->innerHtml . "\n";
            echo $iterator->key() . "=" . $iterator->current()->innerHtml . "\n";
            $iterator->next();
        }
    }


    /**
     * 抓取知乎的一个网页，并进行分析
     */
    public function test2()
    {
        $url = "https://www.zhihu.com/question/25365330";

        $request = \Requests::get($url);

        if (preg_match('#<title>([\s\S]*?)</title>#i', $request->body, $matchs)) {
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

        while ($iterator->valid()) {
            $answer = $iterator->current();

            $dom->load($answer);
            echo $dom->find('.author-link')[0]->text . "\n";
            echo $dom->find('.zm-editable-content')[0]->text;
            echo "\n=============================================\n\n";

            $iterator->next();
        }
    }


    /**
     * 不使用第三方类库实现登陆功能 (不成功，将配置的username改为name即可)(已成功)
     */
    public function test3()
    {
//        $admin_url = 'https://www.zhihu.com';
//        $res = PublicService::call_api($admin_url, [], 'get');
        $config = $this->getConfig();

        $admin_url = 'http://log.lamkakyun.com/admin/login.php?referer=http%3A%2F%2Flog.lamkakyun.com%2Fadmin%2F';
        $res = PublicService::call_api($admin_url, [], 'get');

        if (!preg_match('/<form action="(.*?)"/', $res['data'], $matches)) {
//            die('get login url failed!');
            // 因为cookie存在，所以直接默认是已登录状态
            $admin_url = 'http://log.lamkakyun.com/admin/manage-posts.php';
            $res2 = PublicService::call_api($admin_url);

            echo "=======================\n";
//            var_dump($res2);exit;
            $crawler = new Crawler($res2['data']);
            $crawler->filter('tbody td:nth-child(3)')->each(function ($node) {
                var_dump(trim($node->text()));
            });
            exit;
        }

        $login_url = $matches[1];

        $post_data = $config['website']['loglamkakyuncom'];

        $res = PublicService::call_api($login_url, $post_data);

        var_dump($res);
    }


    /**
     * 使用Zend Client尝试进行 登陆操作 (失败，因为使用username，应该改为name)(已成功)
     */
    public function test4()
    {
        $config = $this->getConfig();

        $url = "http://log.lamkakyun.com/admin/login.php?referer=http%3A%2F%2Flog.lamkakyun.com%2Fadmin%2F";
        // 使用 Zend_Config 读取配置会不会好一点
//        $username = self::$config['website']['loglamkakyuncom']['username'];
//        $password = self::$config['website']['loglamkakyuncom']['password'];

        $http_client = new Client($url);
        $http_client->setCookies([]);
        $res = $http_client->send();

        if (!preg_match('/<form action="(.*?)"/', $res->getBody(), $matches)) {
            die('login url failed!');
        }

        $login_url = $matches[1];

        $http_client->resetParameters();

        $post_data = $config['website']['loglamkakyuncom'];

        $http_client->setUri($login_url);
        $http_client->setMethod('POST');
        $http_client->setParameterPost($post_data);
        $http_client->setOptions(array(
            'maxredirects'    => 10,
            'strictredirects' => true,
            'timeout'         => 30,
            'keepalive'       => true,
//            'encodecookies'   => true,
        ));


        $res2 = $http_client->send();

        var_dump($http_client->getCookies());
//        var_dump($res2->getHeaders());
//        var_dump($res2->getContent());
        var_dump($res2->getStatusCode());

        $admin_url = 'http://log.lamkakyun.com/admin/manage-posts.php';
        $http_client->resetParameters();
        $http_client->setUri($admin_url);
        $res3 = $http_client->send();

//        var_dump($res3->getBody());
        $crawler = new Crawler($res3->getBody());
        $crawler->filter('tbody td:nth-child(3)')->each(function ($node) {
            var_dump(trim($node->text()));
        });
    }


    /**
     * 使用Zend Client尝试进行 登陆操作(失败)
     */
//    public function test5()
//    {
//        $config = $this->getConfig();
//        $admin_url = 'http://log.lamkakyun.com/admin/manage-posts.php';
//
//        $url = "http://log.lamkakyun.com/admin/login.php?referer=http%3A%2F%2Flog.lamkakyun.com%2Fadmin%2F";
//        $client = new Client($url, [
//            'keepalive' => true,
//        ]);
//
//        $res = $client->send();
//
//        if (!preg_match('/<form action="(.*?)"/', $res->getBody(), $matches)) {
//            die('login url failed!');
//        }
//
//        $login_url = $matches[1];
//
//        if (isset($_SESSION['cookiejar']) &&
//            $_SESSION['cookiejar'] instanceof \Zend\Http\Cookies
//        ) {
//
//            $cookieJar = $_SESSION['cookiejar'];
//        } else {
//            // If we don't, authenticate
//            // and store cookies
//            $client->resetParameters(true);
//            $client->setUri($login_url);
//
////            var_dump($config['website']['loglamkakyuncom']);exit;
////            $res = PublicService::call_api($login_url, $config['website']['loglamkakyuncom']);
////            var_dump($res);exit;
//
//            $client->setParameterPost($config['website']['loglamkakyuncom']);
//            $response = $client->setMethod('POST')->send();
//
////            var_dump($response->getStatusCode());exit;
//            $cookieJar = \Zend\Http\Cookies::fromResponse($response, $admin_url);
//
//            // Now, clear parameters and set the URI to the original one
//            // (note that the cookies that were set by the server are now
//            // stored in the jar)
//            $client->resetParameters();
//            $client->setUri($admin_url);
//
//            $client->setCookies($cookieJar->getMatchingCookies($client->getUri()));
//            $response = $client->setMethod('GET')->send();
//            $_SESSION['cookiejar'] = $cookieJar;
//
////            var_dump($response->getBody());
//        }
//    }


    /**
     * 使用 socket 实现登录
     */
    public function test5()
    {

    }


    /**
     * 使用Goutte php 库来爬取知乎数据
     */
    public function test6()
    {
        $url = "https://www.zhihu.com/question/48602044";
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', $url);
        $crawler->filter('title')->each(function ($node) {
            var_dump(trim($node->text())); // 输出标题
        });

        $crawler->filter('.zm-item-answer')->each(function ($node) {
            echo "==================================================\n";
            $node->filter('.author-link')->each(function ($node) {
                var_dump($node->text());
            });
            $node->filter('.zm-editable-content')->each(function ($node) {
                var_dump($node->text());
            });
            $node->filter('.zm-item-vote-info')->each(function ($node) {
                var_dump($node->text());
            });
            echo "==================================================\n";
        });
    }


    /**
     * 使用 Goutte ， 来“点击”链接
     */
    public function test7()
    {
        $url = "http://log.lamkakyun.com/";
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', $url);
        $link = $crawler->selectLink('Zend Framework 1 Usage (2)')->link();
        $crawler = $client->click($link);

        // 点击链接后，获取链接地址的内容
        var_dump($crawler->text());
    }

    /**
     * 使用Goutee，提交表单
     */
    public function test8()
    {
        $config = $this->getConfig();

        $url = "http://log.lamkakyun.com/admin/login.php?referer=http%3A%2F%2Flog.lamkakyun.com%2Fadmin%2F";
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('登录')->form(); // 寻找登录按钮，所在的表单
        $crawler = $client->submit($form, $config['website']['loglamkakyuncom']);
//        var_dump($crawler->text()); // 居然登录成功了！难道之前是因为 一直将 username 当作 name 所以登录不了吗？

        $admin_url = "http://log.lamkakyun.com/admin/manage-posts.php";
        $crawler = $client->request('GET', $admin_url);
//        var_dump($crawler->text());
        $crawler->filter(".typecho-list-table > tbody td:nth-child(3)")->each(function ($node) {
            var_dump(trim($node->text()));
        });
    }


    public function test9()
    {
        for($i=0;$i<2;$i++){
            $pool[] = new MyThread();
        }

        foreach($pool as $worker){
            $worker->start();
        }

        // 父进程等待子进程执行完毕，才能继续执行
        foreach($pool as $worker){
            $worker->join();
        }
    }
}