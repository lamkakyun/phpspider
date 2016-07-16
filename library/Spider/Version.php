<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-16
 * Time: 上午11:42
 */

namespace Spider;

use Zend\Http\Client;
use Zend\Json\Json;

class Version
{
    const VERSION = '0.0.1';

    protected static $latestVersion;

    public static function getCurrent() {
        return self::VERSION;
    }

    public static function getLatest() {
        if (null === self::$latestVersion) {
            self::$latestVersion = 'not available';
            $url = "https://api.github.com/repos/lamkakyun/phpspider/git/refs/tags";

            try {
                $client = new Client($url, array(
                    'adapter' => 'Zend\Http\Client\Adapter\Curl',
                    'timeout'      => 2,
                    'ssltransport' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
                    'sslverifypeer' => false
                ));

                $res = $client->send();

                if ($res->isSuccess()) {
                    $content = $res->getBody();
                }
            } catch (\Exception $e) {
                // do nothing
            }

            if (empty($content)) $content = @file_get_contents($url);

            if (!empty($content)) {
                $res = json_decode($content, Json::TYPE_ARRAY);

                $tags = array_map(
                    function ($tag) {
                        return substr($tag['ref'], 10); // Reliable because we're filtering on 'refs/tags/'
                    },
                    $res
                );

                self::$latestVersion = array_reduce(
                    $tags,
                    function ($a, $b) {
                        return version_compare($a, $b, '>') ? $a : $b;
                    }
                );
            }
        }

        return self::$latestVersion;
    }



    public static function isLatest() {
        return version_compare(Version::getLatest(), Version::getCurrent());
    }
}