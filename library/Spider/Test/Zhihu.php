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
X-Xsrftoken: 4d9ef178241f9f64a2bdabf95f41608b
DNT: 1
Referer: https://www.zhihu.com/
Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,zh-TW;q=0.2
Cookie: d_c0="ADBArfM-QwqPTqg-fC-fCRMlvCLIC_NyOoM=|1469091488"; q_c1=3009d73c3b36406ebac30f8e6f826b4d|1469091488000|1469091488000; _za=cf577d06-dcb1-41dc-b1c5-77f5bd7c0389; _zap=5682cae2-c2fe-4ae5-9b57-13e8ec7663e0; l_cap_id="NmM0MzNlZTg0ZjhkNDQxMWE3ZjZmZmRiODI4YTdjNjM=|1469430453|b3eb287b02f4769e35cb31f69fee8a964056cb54"; cap_id="NGVmMjFlZjE1NzdhNDIxMjg5NDczZjdkZmZlZDFjYWQ=|1469430453|03fcb96ca89fe4fbb43d5cea45824c7d5a52c6cc"; login="NDdlMjY5Njc5ZTY3NDc0OTgxOTVhN2FiMTVkNmQ2NTM=|1469430461|8f0436ae675f3b000bec5943012c545e7b6945a9"; z_c0=Mi4wQUFCQU5tb3lBQUFBTUVDdDh6NURDaGNBQUFCaEFsVk52VWU5VndDVmhxbmxIVy1mb2w3V19qQkVwZlBVMDI4NFpB|1469430461|0d33157bb390bf97ba4c31d383c0f54c520748c6; _xsrf=4d9ef178241f9f64a2bdabf95f41608b; a_t="2.0AABANmoyAAAXAAAALGPBVwAAQDZqMgAAADBArfM-QwoXAAAAYQJVTb1HvVcAlYap5R1vn6Je1v4wRKXz1NNvOGR46bcr6-IfGvRv1u3W4Gqwr5QoYw=="; __utmt=1; __utma=51854390.448005346.1469761575.1469761575.1469761575.1; __utmb=51854390.4.10.1469761575; __utmc=51854390; __utmz=51854390.1469761575.1.1.utmcsr=zhihu.com|utmccn=(referral)|utmcmd=referral|utmcct=/question/22289411; __utmv=51854390.100-1|2=registration_date=20140706=1^3=entry_date=20140706=1
HEADER;


    private static $rootUrl = "https://www.zhihu.com/";

    private static $tmpfile = null;

    private static $href;

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

        $header = preg_replace('/{@url}/', $url_parsed['path'] . (isset($url_parsed['query']) ? '?' . $url_parsed['query'] : ''), self::$postHeader);
        $header = preg_replace('/{@len}/', $content_len, $header);


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
//            unlink(self::$tmpfile);
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

            fwrite($handle, self::requestPostHeader($url, $post_data));
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

            var_dump($content['msg']);

            fclose($fp);
            fclose($handle);

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

        $crawler->filter('.feed-title > a')->each(function ($node) {
            $_url = self::fixPathToAbsolute($node->attr('href'));
            echo $_url;
            if (preg_match('/question/', $_url)) {
                self::requestQuestion($_url);
            }
            echo $node->text() . "\n";
        });

        @unlink(self::$tmpfile);
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
//        $collection = $dom->find('.item')->find('.blk')->find('a');
//        $iterator = $collection->getIterator();
//        echo '总共找到' . $iterator->count() . "个主题\n\n";

        $collection = $dom->find('.item');

        foreach($collection as $item) {
            $a = $item->find('a')[0];
//            echo "\n" . $a->outerHtml . "\n";
//            echo "\n" . $a->getAttribute('href') . '-' . $a->find('strong')->text . "\n";

            // 以二进制的数据存储到Redis 数据库中
            $redis->set('topic:' . $a->getAttribute('href'), $a->find('strong')->text);
        }

//        $keys = $redis->getKeys('topic:*');
//        foreach ($keys as $k) {
//            echo $redis->get($k) . "\n";
//        }
//        var_dump($redis->getMultiple($keys));


        $post_topic_url = 'https://www.zhihu.com/node/TopicsPlazzaListV2';
        $post_data = [
            'method' => 'next',
            'params' => '{"topic_id":253,"offset":20,"hash_id":"d6fe49b4e67984b7f402a7c64a826374"}'
        ];

        self::requestPost($post_topic_url, $post_data);

        echo "Bingo!\n";
    }


    public static function getAnswerAuthorInfo()
    {

    }
}