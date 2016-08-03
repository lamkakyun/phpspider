<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/14/2016
 * Time: 5:31 PM
 */
namespace Application\Service;

use PHPHtmlParser\Dom;
use Snoopy\Snoopy;
use Spider\Test\CounterThread;
use Spider\Test\CounterThread2;
use Spider\Test\CounterThread3;
use Spider\Test\Example;
use Spider\Test\ExampleWorker;
use Spider\Test\LogService;
use Spider\Test\MyThread;
use Spider\Test\MyThread2;
use Spider\Test\PublicService;
use Spider\Test\RedisService;
use Spider\Test\SqlQuery;
use Spider\Test\TweetService;
use Spider\Test\Zhihu;
use Spider\Version;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Zend\Cache\Storage\Adapter\Redis;
use Zend\Cache\StorageFactory;
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
        } catch (\Exception $e) {
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


    /**
     * 测试curl的多线程用法
     */
    public function test07()
    {
        $urls = [
            'https://www.baidu.com',
            'https://www.bing.com',
            'https://www.zhihu.com',
        ];

        $mh = curl_multi_init(); // return multi handle

        foreach ($urls as $key => $url) {
            $con[$key] = curl_init($url);
            curl_setopt($con[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh, $con[$key]);
        }

        // 在整个url请求期间是个死循环，它会轻易导致CPU占用100%。所以修改一下这段代码
//        do {
//            $mrc = curl_multi_exec($mh, $active);
//        } while($active);


        // 让所有curl请求运行起来
        do {
            $mrc = curl_multi_exec($mh, $active); // $mrc 表示 multi request return code
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        //有数据的时候就不停调用curl_multi_exec，暂时没有数据就进入select阶段
        while ($active && $mrc == CURLM_OK) {
            // Wait for activity on any curl-connection
            if (curl_multi_select($mh) == -1) {
                usleep(1);
            }

            // Continue to exec until curl is ready to
            // give us more data
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }


        foreach ($urls as $key => $url) {
            $res[$key] = curl_multi_getcontent($con[$key]);
            curl_close($con[$key]); // 可以不使用curl_close 而使用curl_multi_remove_handle
        }

        print_r($res);
    }


    /**
     * 参考网上的rolling php，写一个并发的curl
     */
    public function test09()
    {
        $urls = [
            'https://www.baidu.com',
            'https://www.bing.com',
            'https://www.zhihu.com',
        ];

        $res = PublicService::rolling_curl($urls);
        var_dump($res);
    }


    /**
     * 尝试通过curl 使用本地socket5代理访问google
     */
    public function test010()
    {
        $url = 'https://twitter.com/';
        $proxy = '127.0.0.1:10800';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME); // 本地只能用 CURLPROXY_SOCKS5_HOSTNAME
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        $res = curl_exec($ch);
        curl_close($ch);
        var_dump($res);
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
     * 使用 socket 实现登录 log.lamkakyun.com
     * 妈的，只要我在我的浏览器登录，然后将header复制过来，就直接跳过登录了，厉害啊
     */
    public function test5()
    {
        $server = "tcp://log.lamkakyun.com:80";
        $handle = stream_socket_client($server, $errno, $errstr, 30);

        if (!$handle) {
            echo "$errstr ($errno)<br />\n";
        } else {
//            fwrite($handle, "GET /admin/login.php?referer=http%3A%2F%2Flog.lamkakyun.com%2Fadmin%2F HTTP/1.1\r\nHost: log.lamkakyun.com\r\nAccept: */*\r\nConnection: Close\r\n\r\n");

            // 删除 Accept-Encoding:gzip, deflate, sdch
            $header = <<<HEADER
GET /admin/manage-posts.php HTTP/1.1
Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cache-Control:no-cache
Connection:keep-alive
Cookie:read-mode=false; PHPSESSID=6op5rakrfuhhbm92aihrc655c2; __utmt=1; __utma=27437473.721865312.1469095701.1469678333.1469689437.3; __utmb=27437473.2.10.1469689437; __utmc=27437473; __utmz=27437473.1469095701.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); 6073f245f4708a3f9ac2e540bc40a8ad__typecho_uid=1; 6073f245f4708a3f9ac2e540bc40a8ad__typecho_authCode=%24T%24W4NCIvTuCf3916348c42531df0d5ae9122c333a12
DNT:1
Host:log.lamkakyun.com
Pragma:no-cache
Referer:http://log.lamkakyun.com/admin/
Upgrade-Insecure-Requests:1
User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36\r\n\r\n
HEADER;

            fwrite($handle, $header);
            while (!feof($handle)) {
                echo fgets($handle, 1024);
            }
            fclose($handle);
        }
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


    /**
     * 线程的使用 （基本使用）
     */
    public function test9()
    {
        for ($i = 0; $i < 2; $i++) {
            $pool[] = new MyThread();
        }

        foreach ($pool as $worker) {
            $worker->start();
        }

        // 父进程等待子进程执行完毕，才能继续执行
        foreach ($pool as $worker) {
            $worker->join();
        }
    }

    /**
     * 线程 worker 和 stackable
     * 将stackable实例，放到worker的stack里面，然后运行
     */
    public function test10()
    {
        $worker = new ExampleWorker();

        $work1 = new SqlQuery('select * from album');
        $worker->stack($work1);

        $work2 = new SqlQuery('select * from posts');
        $worker->stack($work2);

        $worker->start();
        $worker->shutdown();

        var_dump($worker->isShutdown());
    }

    /**
     * 测试互斥锁，制多个线程同一时刻只能有一个线程工作的情况下可以使用
     */
    public function test11()
    {
        $counter = 0;
        $handle = fopen('/tmp/phpspider/counter.txt', 'w');
        fwrite($handle, $counter);
        fclose($handle);

        // 没有互斥锁
        echo "【没有互斥锁】\n";
        for ($i = 0; $i < 50; $i++) {
            $threads[$i] = new CounterThread();
            $threads[$i]->start();
        }

        //加入互斥锁
        echo "【加入互斥锁】\n";

        $mutex = \Mutex::create(true);
        for ($i = 0; $i < 50; $i++) {
            $threads[$i] = new CounterThread($mutex);
            $threads[$i]->start();

        }
        \Mutex::unlock($mutex);
        for ($i = 0; $i < 50; $i++) {
            $threads[$i]->join(); // 也就是说，没有线程阻塞，主进程就会直接结束，导致死锁
        }
        \Mutex::destroy($mutex);
    }

    /**
     * 多线程与共享内存，没有使用任何锁，(在没有sleep的情况下,仍然可能正常工作)，内存操作本身不具备锁的功能。
     * @desc 如果报错 shhm_attach函数不存在,则需要重新编译php
     * /configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc --with-mysqli --with-iconv-dir --with-freetype-dir --with-jpeg-dir --with-png-dir --with-libxml-dir --enable-xml --disable-rpath --enable-bcmath --enable-shmop --enable-sysvsem --enable-inline-optimization --with-curl --enable-mbregex --enable-fpm --enable-mbstring --with-mcrypt --with-gd --enable-gd-native-ttf --with-openssl --with-mhash --enable-pcntl --enable-sockets --with-ldap --with-ldap-sasl --with-xmlrpc --enable-zip --enable-soap --without-pear --with-zlib --enable-pdo --with-pdo-mysql --with-mysql --enable-maintainer-zts --enable-sysvmsg=shared --enable-sysvshm=shared --enable-sysvsem=shared
     * 在php.ini添加 ;extension=sysvmsg.so;extension=sysvsem.so;extension=sysvshm.so
     */
    public function test12()
    {
        $tmp = tempnam(__FILE__, 'PHP');
        $key = ftok($tmp, 'a');
        $shmid = shm_attach($key); // 创建共享内存
        $shmkey = 1;
        $counter = 0;
        shm_put_var($shmid, $shmkey, $counter); // key = 1 put入数据 $counter的值

        for ($i = 0; $i < 100; $i++) {
            $threads[] = new CounterThread2($shmid, $shmkey);
        }
        for ($i = 0; $i < 100; $i++) {
            $threads[$i]->start();
        }
        for ($i = 0; $i < 100; $i++) {
            $threads[$i]->join();
        }
        shm_remove($shmid);
        shm_detach($shmid);
    }

    /**
     * 线程同步 (失败，counter 错误)
     * @desc 有些场景我们不希望 thread->start() 就开始运行程序，而是希望线程等待我们的命令。$thread->wait();测作用是 thread->start()后线程并不会立即运行，只有收到 $thread->notify(); 发出的信号后才运行
     */
    public function test13()
    {
        $tmp = tempnam(__FILE__, 'PHP');
        $key = ftok($tmp, 'a');
        $shmid = shm_attach($key);
        $shmkey = 1;
        $counter = 0;
        shm_put_var($shmid, $shmkey, $counter);

        for ($i = 0; $i < 100; $i++) {
            $threads[] = new CounterThread3($shmid, $shmkey);
        }

        for ($i = 0; $i < 100; $i++) {
            $threads[$i]->start();
            $threads[$i]->join();
        }


//        for ($i = 0; $i < 100; $i ++) {
//            var_dump($threads[ $i ]->isWaiting());
//        }

        for ($i = 0; $i < 100; $i++) {
            $threads[$i]->synchronized(function ($thread) {
                $thread->notify();
            }, $threads[$i]);
        }

//        for ($i = 0; $i < 100; $i ++) {
//            var_dump($threads[ $i ]->isWaiting());
//        }


        shm_remove($shmid);
        shm_detach($shmid);
    }

    /**
     * 测试同步块问题
     */
    public function test14()
    {
        for ($i = 0; $i < 10; $i++) {
            $thread[$i] = new MyThread2();
            $thread[$i]->start();
//            $thread[$i]->join();
        }
    }

    /**
     * 尝试登录知乎 (失败， Symfony-Crawler, Snoopy, SimpleBrowser 均是失败告终，好失败啊)
     * 终于使用socket 直接跳过登录，直接在浏览器中登录，复制header 即可
     */
    public function test15()
    {
        $server = "ssl://www.zhihu.com:443";
        $header = <<<HEADER
GET / HTTP/1.1
Host: www.zhihu.com
Connection: keep-alive
Pragma: no-cache
Cache-Control: no-cache
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
DNT: 1
Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cookie: d_c0="ADBArfM-QwqPTqg-fC-fCRMlvCLIC_NyOoM=|1469091488"; q_c1=3009d73c3b36406ebac30f8e6f826b4d|1469091488000|1469091488000; _za=cf577d06-dcb1-41dc-b1c5-77f5bd7c0389; _zap=5682cae2-c2fe-4ae5-9b57-13e8ec7663e0; l_cap_id="NmM0MzNlZTg0ZjhkNDQxMWE3ZjZmZmRiODI4YTdjNjM=|1469430453|b3eb287b02f4769e35cb31f69fee8a964056cb54"; cap_id="NGVmMjFlZjE1NzdhNDIxMjg5NDczZjdkZmZlZDFjYWQ=|1469430453|03fcb96ca89fe4fbb43d5cea45824c7d5a52c6cc"; login="NDdlMjY5Njc5ZTY3NDc0OTgxOTVhN2FiMTVkNmQ2NTM=|1469430461|8f0436ae675f3b000bec5943012c545e7b6945a9"; z_c0=Mi4wQUFCQU5tb3lBQUFBTUVDdDh6NURDaGNBQUFCaEFsVk52VWU5VndDVmhxbmxIVy1mb2w3V19qQkVwZlBVMDI4NFpB|1469430461|0d33157bb390bf97ba4c31d383c0f54c520748c6; _xsrf=4d9ef178241f9f64a2bdabf95f41608b; a_t="2.0AABANmoyAAAXAAAAZce_VwAAQDZqMgAAADBArfM-QwoXAAAAYQJVTb1HvVcAlYap5R1vn6Je1v4wRKXz1NNvOGSYVOG657GSrX2MO-Ozi35N2euVsA=="; s-q=%E6%AC%A7%E9%98%B3%E9%94%8B; s-i=13; sid=m8l66a99; __utma=51854390.2104039469.1469688017.1469688017.1469695819.2; __utmb=51854390.6.10.1469695819; __utmc=51854390; __utmz=51854390.1469688017.1.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); __utmv=51854390.100-1|2=registration_date=20140706=1^3=entry_date=20140706=1\r\n\r\n
HEADER;

        $handle = stream_socket_client($server, $errno, $errstr, 30);
        fwrite($handle, $header);
        while (!feof($handle)) {
            $_str = fgets($handle, 1024);
            if (preg_match('#</body>#', $_str)) break;
            echo $_str;
        }

        fclose($handle);
    }

    private static function genHeaders($url, $method = 'GET', $data = [])
    {
        $url_parsed = parse_url($url);
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST'])) throw new \Exception('method error');

        $header = <<<HEADER
%s %s HTTP/1.1
Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cache-Control:no-cache
Connection:keep-alive
DNT:1
Host:%s
Pragma:no-cache
Upgrade-Insecure-Requests:1
User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36
HEADER;


        $header = sprintf($header, $method, $url_parsed['path'] . (isset($url_parsed['query']) ? '?' . $url_parsed['query'] : ''), $url_parsed['host']);

        if (!empty($data)) {
            $header .= http_build_query($data);
        }

        return $header . "\r\n\r\n";
    }

    /**
     * test5，重写，直接使用socket 登录, 失败，又是失败
     */
    public function test16()
    {
        $server = "tcp://log.lamkakyun.com:80";
        $url = "http://log.lamkakyun.com/admin/";
        $header = self::genHeaders($url);
        $handle = stream_socket_client($server, $errno, $errstr, 30);

        if (!$handle) {
            echo "$errstr ($errno)<br />\n";
        } else {


            fwrite($handle, $header);
            $redirect_url = null;
            while (!feof($handle)) {
                $_str = fgets($handle, 1024);
                if (preg_match("/^Location:([\s\S]*)/", $_str, $matches)) {
                    $redirect_url = trim($matches[1]);
                    break;
                }
            }
//            fclose($handle);

            // 访问3xx的链接
            $login_url = null;
            if ($redirect_url) {
                $_header = self::genHeaders($redirect_url);

                fwrite($handle, $_header);

                while (!feof($handle)) {
                    $_str = fgets($handle, 1024);
//                    echo 'output:  ' . $_str;
                    if (preg_match('#<form action="([^"]*)"#', $_str, $matches)) {
                        $login_url = $matches[1];
                        break;
                    }
                }

            }

            fclose($handle);
        }
    }

    /**
     * 获取知乎首页信息
     */
    public function test17()
    {
        Zhihu::requestRoot();
    }

    /**
     * 模拟知乎获取更多回答 POST
     */
    public function test18()
    {
        Zhihu::getFeedList();
    }

    /**
     * 获取知乎所有用户的信息(该方法被取消，因为测试写的太乱，仅用来测试)
     * 1. 进入topic list, 加载所有topic, 保存到队列（redis）
     * 2. 进入topic-top-answers，提取前10页所有问题
     * 3. 进入问题,加载100个回答
     * 4. 进入所有回答者的主页，获取回答者的信息,保存到数据库
     */
    public function test19()
    {
        $config = $this->getConfig();
//        Zhihu::setRedis(new RedisService($config['redis-config']));
//        Zhihu::setRedis(new Redis($config['redis-config']));
//        Zhihu::setRedis(StorageFactory::factory($config['redis-config']));
        Zhihu::setRedis($config['redis-config']);
        Zhihu::getAnswerAuthorInfo_bak();
    }


    /**
     * 获取知乎所有用户的信息(该方法被取消，因为测试写的太乱，仅用来测试)
     * 通过线程或者进程的方式读取redis中的队列，以保证爬虫的速度
     * 1. 进入topic list, 加载所有topic, 保存到队列（redis）
     * 2. 进入topic-top-answers，提取前10页所有问题
     * 3. 进入问题,加载100个回答
     * 4. 进入所有回答者的主页，获取回答者的信息,保存到数据库
     */
    public function test20()
    {
        $config = $this->getConfig();
        Zhihu::setRedis($config['redis-config']);
        Zhihu::getAnswerAuthorInfo();
    }
}