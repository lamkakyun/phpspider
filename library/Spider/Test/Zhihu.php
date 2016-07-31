<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/28/2016
 * Time: 5:48 PM
 */

namespace Spider\Test;

use PHPHtmlParser\Dom;
use Symfony\Component\DomCrawler\Crawler;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Cache\Storage\Adapter\Redis;
use Zend\Dom\Document;
use Zend\Dom\Document\Query;

class Zhihu
{

    private static $server = "ssl://www.zhihu.com:443";

    private static $redisConfig;

    /**
     * 删除Accept-Encoding: gzip, deflate, br
     * @var string
     */
    private static $getHeader = <<<HEADER
GET %s HTTP/1.1
Host: www.zhihu.com
Connection: keep-alive
Pragma: no-cache
Cache-Control: no-cache
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
DNT: 1
Referer: https://www.zhihu.com/
Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cookie: d_c0="ADBArfM-QwqPTqg-fC-fCRMlvCLIC_NyOoM=|1469091488"; q_c1=3009d73c3b36406ebac30f8e6f826b4d|1469091488000|1469091488000; _za=cf577d06-dcb1-41dc-b1c5-77f5bd7c0389; _zap=5682cae2-c2fe-4ae5-9b57-13e8ec7663e0; l_cap_id="NmM0MzNlZTg0ZjhkNDQxMWE3ZjZmZmRiODI4YTdjNjM=|1469430453|b3eb287b02f4769e35cb31f69fee8a964056cb54"; cap_id="NGVmMjFlZjE1NzdhNDIxMjg5NDczZjdkZmZlZDFjYWQ=|1469430453|03fcb96ca89fe4fbb43d5cea45824c7d5a52c6cc"; login="NDdlMjY5Njc5ZTY3NDc0OTgxOTVhN2FiMTVkNmQ2NTM=|1469430461|8f0436ae675f3b000bec5943012c545e7b6945a9"; z_c0=Mi4wQUFCQU5tb3lBQUFBTUVDdDh6NURDaGNBQUFCaEFsVk52VWU5VndDVmhxbmxIVy1mb2w3V19qQkVwZlBVMDI4NFpB|1469430461|0d33157bb390bf97ba4c31d383c0f54c520748c6; _xsrf=4d9ef178241f9f64a2bdabf95f41608b; a_t="2.0AABANmoyAAAXAAAALGPBVwAAQDZqMgAAADBArfM-QwoXAAAAYQJVTb1HvVcAlYap5R1vn6Je1v4wRKXz1NNvOGR46bcr6-IfGvRv1u3W4Gqwr5QoYw=="; __utmt=1; __utma=51854390.448005346.1469761575.1469773278.1469773278.3; __utmb=51854390.3.9.1469777173463; __utmc=51854390; __utmz=51854390.1469773278.2.2.utmcsr=zhihu.com|utmccn=(referral)|utmcmd=referral|utmcct=/question/22289411; __utmv=51854390.100-1|2=registration_date=20140706=1^3=entry_date=20140706=1
HEADER;

    /**
     * 删除Accept-Encoding: gzip, deflate, br
     * @var string
     */
    private static $postHeader = <<<HEADER
POST {@url} HTTP/1.1
Host: www.zhihu.com
Connection: keep-alive
Content-Length: {@len}
Pragma: no-cache
Cache-Control: no-cache
Origin: https://www.zhihu.com
User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36
Content-Type: application/x-www-form-urlencoded; charset=UTF-8
Accept: */*
X-Requested-With: XMLHttpRequest
X-Xsrftoken: {@token}
DNT: 1
Referer: https://www.zhihu.com/
Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cookie: d_c0="ADBArfM-QwqPTqg-fC-fCRMlvCLIC_NyOoM=|1469091488"; q_c1=3009d73c3b36406ebac30f8e6f826b4d|1469091488000|1469091488000; _za=cf577d06-dcb1-41dc-b1c5-77f5bd7c0389; _zap=5682cae2-c2fe-4ae5-9b57-13e8ec7663e0; l_cap_id="NmM0MzNlZTg0ZjhkNDQxMWE3ZjZmZmRiODI4YTdjNjM=|1469430453|b3eb287b02f4769e35cb31f69fee8a964056cb54"; cap_id="NGVmMjFlZjE1NzdhNDIxMjg5NDczZjdkZmZlZDFjYWQ=|1469430453|03fcb96ca89fe4fbb43d5cea45824c7d5a52c6cc"; login="NDdlMjY5Njc5ZTY3NDc0OTgxOTVhN2FiMTVkNmQ2NTM=|1469430461|8f0436ae675f3b000bec5943012c545e7b6945a9"; z_c0=Mi4wQUFCQU5tb3lBQUFBTUVDdDh6NURDaGNBQUFCaEFsVk52VWU5VndDVmhxbmxIVy1mb2w3V19qQkVwZlBVMDI4NFpB|1469430461|0d33157bb390bf97ba4c31d383c0f54c520748c6; _xsrf={@token}; a_t="2.0AABANmoyAAAXAAAALGPBVwAAQDZqMgAAADBArfM-QwoXAAAAYQJVTb1HvVcAlYap5R1vn6Je1v4wRKXz1NNvOGR46bcr6-IfGvRv1u3W4Gqwr5QoYw=="; __utmt=1; __utma=51854390.448005346.1469761575.1469761575.1469761575.1; __utmb=51854390.4.10.1469761575; __utmc=51854390; __utmz=51854390.1469761575.1.1.utmcsr=zhihu.com|utmccn=(referral)|utmcmd=referral|utmcct=/question/22289411; __utmv=51854390.100-1|2=registration_date=20140706=1^3=entry_date=20140706=1
HEADER;


    private static $rootUrl = "https://www.zhihu.com/";

    private static $tmpfile = null;

    private static $href;

    private static $xsrfToken;

    /**
     * 获取http get方法的请求头
     * @param $url
     * @return string
     */
    public static function requestGetHedaer($url)
    {
        $url_parsed = parse_url($url);

//        $header = sprintf(self::$getHeader, $url_parsed['path'] . (isset($url_parsed['query']) ? '?' . $url_parsed['query'] : ''));
        $header = preg_replace('/%s/', $url_parsed['path'] . (isset($url_parsed['query']) ? '?' . $url_parsed['query'] : ''), self::$getHeader);

        return $header . "\r\n\r\n";
    }


    public static function requestPostHeader($url, $data = [])
    {
        $url_parsed = parse_url($url);
        $post_data = http_build_query($data);
        $content_len = strlen($post_data);

        if (!self::$xsrfToken) {
//            throw new \Exception('no xsrf token');
//            exit;
            self::setToken();
        }

        $header = preg_replace('/{@url}/', $url_parsed['path'] . (isset($url_parsed['query']) ? '?' . $url_parsed['query'] : ''), self::$postHeader);
        $header = preg_replace('/{@len}/', $content_len, $header);
        $header = preg_replace('/{@token}/', self::$xsrfToken, $header);


        return $header . "\r\n\r\n";
    }

    protected static function requestGet($url)
    {
        self::$tmpfile = tempnam(sys_get_temp_dir(), 'PHPSPIDER');
        $fp = fopen(self::$tmpfile, 'a');
        $handle = stream_socket_client(self::$server, $errno, $errstr, 30);
        try {
            if (!$handle) {
                throw new \Exception("$errstr ($errno)<br />\n");
            }

            fwrite($handle, self::requestGetHedaer($url));

            $flag = false;
            while (!feof($handle)) {
                $_str = fgets($handle, 1024);

                if (!$flag && !preg_match('/^<!DOCTYPE/', $_str)) continue;
                else $flag = true;

                fputs($fp, $_str);
                if (preg_match("/<\/body>/", $_str, $matches)) {
                    break;
                }
            }
            fclose($handle);
//            @unlink(self::$tmpfile);
            fclose($fp);
        } catch(\Exception $e) {
            fclose($handle);
            fclose($fp);
            echo $e->getMessage();
        }
    }


    protected static function requestPost($url, $post_data)
    {
        self::$tmpfile = tempnam(sys_get_temp_dir(), 'PHPSPIDER');
        $fp = fopen(self::$tmpfile, 'a');
        $post_data_str = http_build_query($post_data);
        $handle = stream_socket_client(self::$server, $errno, $errstr, 30);

        try {
            if (!$handle) {
                throw new \Exception("$errstr ($errno)<br />\n");
            }
//            stream_set_timeout($handle, 2);
            $header = self::requestPostHeader($url, $post_data);

            fwrite($handle, $header);
            fwrite($handle, $post_data_str);

//            stream_set_blocking($handle, true); // 设置阻塞模式，timeout才起作用
            stream_set_blocking($handle, false); // 非阻塞模式
            while (!feof($handle)) {
                $_str = fread($handle, 1024);
                fputs($fp, trim($_str));
                if (preg_match('/.*?}$/', $_str)) break; // 遇到json 对象 结束符，退出
            }

            $content = file_get_contents(self::$tmpfile);

            if (preg_match('/{[\s\S]*/', $content, $matches)) {
                $content = $matches[0];
                $content = json_decode($content, true);
            } else {
                echo 'no match';
            }

            var_dump($content);exit;
            $ret_content = "";
            foreach ($content['msg'] as $item) {
                $ret_content .= $item;
            }

            fclose($fp);
            fclose($handle);
//            @unlink(self::$tmpfile);

            return $ret_content;

        } catch(\Exception $e) {
            fclose($handle);
            fclose($fp);
            echo $e->getMessage();
        }
    }

    public static function requestRoot()
    {
        self::requestGet(self::$rootUrl);
        $content = file_get_contents(self::$tmpfile);
        $crawler = new Crawler($content);
        $crawler->filter('title')->each(function ($node) {
            echo $node->text() . "\n";
        });

        $crawler->filter('input[name=_xsrf]')->each(function($node) {
           self::$xsrfToken = $node->attr('value');
        });

        $crawler->filter('.feed-title > a')->each(function ($node) {
            $_url = self::fixPathToAbsolute($node->attr('href'));
            echo $_url;
            if (preg_match('/question/', $_url)) {
                self::requestQuestion($_url);
            }
            echo $node->text() . "\n";
        });

//        @unlink(self::$tmpfile);
    }

    public static function setToken() {
        self::requestGet(self::$rootUrl);
        $content = file_get_contents(self::$tmpfile);
        $crawler = new Crawler($content);

        $crawler->filter('input[name=_xsrf]')->each(function($node) {
            self::$xsrfToken = $node->attr('value');
        });
    }

    /**
     * 使用post方法获取feed
     */
    public static function getFeedList()
    {
        $url = "https://www.zhihu.com/node/TopStory2FeedList";

        $post_data = [
            'params' => '{"offset":10,"start":"9"}',
            'method' => 'next',
        ];

        self::requestPost($url, $post_data);
        @unlink(self::$tmpfile);
    }

    public static function requestQuestion($url)
    {
        $url_parsed = parse_url($url);
        self::requestGet(self::fixPathToAbsolute($url_parsed['path']));
        $content = file_get_contents(self::$tmpfile);

        $crawler = new Crawler($content);
        $crawler->filter('.author-link')->each(function ($node) {
            echo $node->text() . "\n";
        });
    }


    public static function fixPathToAbsolute($url)
    {
        $ret_url = $url;
        if (!preg_match('/^http/', $url)) {
            $ret_url = rtrim(self::$rootUrl, '/') . '/' . ltrim($url, '/');
        }

        return $ret_url;
    }

    public static function setRedis(array $config)
    {
        self::$redisConfig = $config;
    }

    public static function getAnswerAuthorInfo_bak()
    {
//        self::$redis->setItem('hello', 'world');
//        exit;

        $redis = new \Redis();

        if (!$redis->connect(self::$redisConfig['adapter']['options']['server']['host'])) {
            die('redis connect failed!');
        }

//        $redis->select(0);

        $topic_url = "https://www.zhihu.com/topics";
        self::requestGet($topic_url);
        $content = file_get_contents(self::$tmpfile);
//        $crawler = new Crawler($content);
//        $crawler->filter('.item > .blk > a:first-child')->each(function ($node) {
//            self::$href = $node->attr('href') . "\n";
//            $node->filter('strong')->each(function ($n) {
////                echo $n->text() . "\n";
//                $text = $n->text();
////                self::$redis->setItem(self::$href, $text);
//
//            });
//        });

        $dom = new Dom();
        $dom->load($content);

        self::$xsrfToken = $dom->find('input[name=_xsrf]')->getAttribute('value');

        $data_init = $dom->find('.zh-general-list')->getAttribute('data-init');
        $data_init = json_decode(html_entity_decode($data_init), true);
//        var_dump($data_init);

//        $collection = $dom->find('.item')->find('.blk')->find('a');
//        $iterator = $collection->getIterator();
//        echo '总共找到' . $iterator->count() . "个主题\n\n";

        $collection = $dom->find('.item');

        foreach($collection as $item) {
            $a = $item->find('a')[0];
//            echo "\n" . $a->outerHtml . "\n";
            echo "\n" . $a->getAttribute('href') . '-' . $a->find('strong')->text . "\n";

            // 以二进制的数据存储到Redis 数据库中
//            $redis->set('topic:' . $a->getAttribute('href'), $a->find('strong')->text);
        }

//        $keys = $redis->getKeys('topic:*');
//        foreach ($keys as $k) {
//            echo $redis->get($k) . "\n";
//        }
//        var_dump($redis->getMultiple($keys));


        $post_topic_url = 'https://www.zhihu.com/node/TopicsPlazzaListV2';
        $post_data = [
            'method' => 'next',
            'params' => '{"topic_id":' . $data_init['params']['topic_id'] . ',"offset":60,"hash_id":"' . $data_init['params']['hash_id'] . '"}'
        ];

        $post_content = self::requestPost($post_topic_url, $post_data);

//        var_dump($post_content);

        $dom->load($post_content);
        $collection2 = $dom->find('.item');
        echo "\n===================================\n";
        foreach($collection as $item) {
            $a = $item->find('a')[0];
//            $redis->set('topic:' . $a->getAttribute('href'), $a->find('strong')->text);
            echo "\n" . $a->getAttribute('href') . '-' . $a->find('strong')->text . "\n";
        }

        @exec('rm -rf /tmp/PHPSPIDER*');
        echo "Bingo!\n";
    }




    protected static $topic_header = <<<HEADER
GET /topics HTTP/1.1
Host: www.zhihu.com
Connection: keep-alive
Pragma: no-cache
Cache-Control: no-cache
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
DNT: 1
Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cookie: d_c0="AJBAQjdwEgqPTpRkTpLYxg8_cymMjex7-Wk=|1465816068"; _zap=941096ac-a7f7-4668-a75f-0008e22e8da9; _za=66b72be9-eade-4057-ab75-754f011fb536; q_c1=e3d88c88ef994381b6db565c294f99c1|1468512512000|1465816067000; l_cap_id="ODAxMWZmZjg1YzFmNDczOGI5NmE4NWE2NTQyOGQzOTk=|1468607089|9277822997bdd9795d11bf887217bc03c8ea9b64"; cap_id="Njc0MDM5MGIwMTM4NGVjNGIyZmE3MGY3YmJiYTU3Y2I=|1468607089|a9c7aecc8982a1f4f430bd2d9dd770c44d6a9436"; login="NGVhZDY2MDFkM2FiNGI0NmE3NWNkOTE2ZTQ3NzZhNTk=|1468607092|d356dcd778628b867ffc2b2ea54121dbf9584c79"; z_c0=Mi4wQUFCQU5tb3lBQUFBa0VCQ04zQVNDaGNBQUFCaEFsVk5kTGV3VndDOEtaSUpoMUU3N3lGclppU1N3REZ3eGp5ZXp3|1468607092|554077db8740833c3edac27698d84eb3ef261006; _xsrf=9bf8162bd980e92c44fab9ea52cd3751; __utmt=1; a_t="2.0AABANmoyAAAXAAAAq0XFVwAAQDZqMgAAAJBAQjdwEgoXAAAAYQJVTXS3sFcAvCmSCYdRO-8ha2YkksAxcMY8ns88v5znb-E6xNzNgFHHF3Tdg4jAFA=="; __utma=51854390.858669824.1469201753.1469856286.1469953378.8; __utmb=51854390.11.9.1469954240455; __utmc=51854390; __utmz=51854390.1469953378.8.6.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); __utmv=51854390.100-1|2=registration_date=20140706=1^3=entry_date=20140706=1\r\n\r\n
HEADER;


    protected static $topic_header_post = <<<HEADER
POST /node/TopicsPlazzaListV2 HTTP/1.1
Host: www.zhihu.com
Connection: keep-alive
Content-Length: {@len}
Pragma: no-cache
Cache-Control: no-cache
Origin: https://www.zhihu.com
User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36
Content-Type: application/x-www-form-urlencoded; charset=UTF-8
Accept: */*
X-Requested-With: XMLHttpRequest
X-Xsrftoken: 9bf8162bd980e92c44fab9ea52cd3751
DNT: 1
Referer: https://www.zhihu.com/topics
Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cookie: d_c0="AJBAQjdwEgqPTpRkTpLYxg8_cymMjex7-Wk=|1465816068"; _zap=941096ac-a7f7-4668-a75f-0008e22e8da9; _za=66b72be9-eade-4057-ab75-754f011fb536; q_c1=e3d88c88ef994381b6db565c294f99c1|1468512512000|1465816067000; l_cap_id="ODAxMWZmZjg1YzFmNDczOGI5NmE4NWE2NTQyOGQzOTk=|1468607089|9277822997bdd9795d11bf887217bc03c8ea9b64"; cap_id="Njc0MDM5MGIwMTM4NGVjNGIyZmE3MGY3YmJiYTU3Y2I=|1468607089|a9c7aecc8982a1f4f430bd2d9dd770c44d6a9436"; login="NGVhZDY2MDFkM2FiNGI0NmE3NWNkOTE2ZTQ3NzZhNTk=|1468607092|d356dcd778628b867ffc2b2ea54121dbf9584c79"; z_c0=Mi4wQUFCQU5tb3lBQUFBa0VCQ04zQVNDaGNBQUFCaEFsVk5kTGV3VndDOEtaSUpoMUU3N3lGclppU1N3REZ3eGp5ZXp3|1468607092|554077db8740833c3edac27698d84eb3ef261006; _xsrf=9bf8162bd980e92c44fab9ea52cd3751; a_t="2.0AABANmoyAAAXAAAAf0jFVwAAQDZqMgAAAJBAQjdwEgoXAAAAYQJVTXS3sFcAvCmSCYdRO-8ha2YkksAxcMY8ns8P4ZWNxkdLQ9wC0BPquwE27tf3ZA=="; __utma=51854390.858669824.1469201753.1469856286.1469953378.8; __utmb=51854390.19.9.1469954240455; __utmc=51854390; __utmz=51854390.1469953378.8.6.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); __utmv=51854390.100-1|2=registration_date=20140706=1^3=entry_date=20140706=1\r\n\r\n
HEADER;


    public static function getAnswerAuthorInfo()
    {

        self::$tmpfile = tempnam(sys_get_temp_dir(), 'PHPSPIDER');
        $fp = fopen(self::$tmpfile, 'a');
        $handle = stream_socket_client(self::$server, $errno, $errstr, 30);
        try {
            if (!$handle) {
                throw new \Exception("$errstr ($errno)<br />\n");
            }
            echo "start...\n";

            fwrite($handle, self::$topic_header);

            $flag = false;
            while (!feof($handle)) {
                $_str = fgets($handle, 1024);

                if (!$flag && !preg_match('/^<!DOCTYPE/', $_str)) continue;
                else $flag = true;

                fputs($fp, $_str);
                if (preg_match("/<\/body>/", $_str, $matches)) {
                    break;
                }
            }

            $content = file_get_contents(self::$tmpfile);

//            $dom = new Dom();
//            $dom->load($content);
//            $collection = $dom->find('.item');
//
//            foreach($collection as $item) {
//                $a = $item->find('a')[0];
//                echo "\n" . $a->getAttribute('href') . '-' . $a->find('strong')->text . "\n";
//            }

            $finder = new Query();
            $dom = new Document($content);
            $results = $finder->execute('.item a[target="_blank"]', $dom, Query::TYPE_CSS);
            foreach ($results as $r) {
                echo $r->attributes->getNamedItem('href')->textContent . ' - ' . $r->textContent;
            }

            fclose($fp);
            @unlink(self::$tmpfile);
            fclose($handle);

            echo "\nmore topics\n";

            for ($i = 20;;$i += 20) {
                echo $i . "\n";
                $post_data = [
                    'method' => 'next',
                    'params' => '{"topic_id":253,"offset":' . $i . ',"hash_id":"d6fe49b4e67984b7f402a7c64a826374"}'
                ];

                $post_data_str = http_build_query($post_data);
                $tmp_header = preg_replace('/{@len}/', strlen($post_data_str), self::$topic_header_post);

                self::$tmpfile = tempnam(sys_get_temp_dir(), 'PHPSPIDER');
                $fp = fopen(self::$tmpfile, 'a');

                $handle = stream_socket_client(self::$server, $errno, $errstr, 30);
                fwrite($handle, $tmp_header);
                fwrite($handle, $post_data_str);

                stream_set_blocking($handle, false); // 非阻塞模式
                while (!feof($handle)) {
                    $_str = fread($handle, 1024);
                    fputs($fp, trim($_str));
                    if (preg_match('/.*?}$/', $_str)) break; // 遇到json 对象 结束符，退出
                }

                $content = file_get_contents(self::$tmpfile);
                if (preg_match('/{[\s\S]*/', $content, $matches)) {
                    $content = $matches[0];
                    $content = json_decode($content, true);
                } else {
                    echo 'no match';
                }
                $all_content = "";
                foreach ($content['msg'] as $item) {
                    $all_content .= $item . "\n";
                }
//                $dom->load($all_content);
//                $collection = $dom->find('.item');
//                if ($i == 120) {
////                    $domdocument = new \DOMDocument();
////                    $domdocument->loadHTML($all_content);
////                    $elements = $domdocument->getElementsByTagName('a');
//
////                    foreach($elements as $e) {
////                        echo $e->getAttribute('href');
////                    }
//                    echo $i . "\n";
////                    var_dump($elements->length);
////                    var_dump($all_content);
//                    var_dump(count($collection));
//                    exit;
//                }

//                foreach($collection as $item) {
//                    $a = $item->find('a')[0];
////                    echo "\n" . $a->getAttribute('href') . '-' . $a->find('strong')->text . "\n";
//                }

                if ($i == 120) {

//                    var_dump($all_content);exit;
                    $finder = new Query();
                    $dom = new Document($all_content, Document::DOC_HTML, 'UTF-8');
                    $results = $finder->execute('.item a[target="_blank"]', $dom, Query::TYPE_CSS);
                    foreach ($results as $r) {
//                    echo $r->attributes->getNamedItem('href')->textContent . ' - ' . iconv('UTF8', 'gb2312', $r->textContent);
                        echo $r->attributes->getNamedItem('href')->textContent . " - " . $r->textContent;
                    }
                    exit;
                }


                fclose($handle);
                @unlink(self::$tmpfile);
                fclose($fp);

                if (empty($all_content)) break;
            }


        } catch(\Exception $e) {
            fclose($handle);
            @unlink(self::$tmpfile);
            fclose($fp);
            echo "Exception:" . $e->getMessage();
        }
    }
}