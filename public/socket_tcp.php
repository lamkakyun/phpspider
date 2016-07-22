<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 6:08 PM
 */

// 直接访问 百度 首页
$fp = stream_socket_client("tcp://www.baidu.com:80", $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "GET / HTTP/1.0\r\nHost: www.baidu.com\r\nAccept: */*\r\n\r\n");
    while (!feof($fp)) {
        echo fgets($fp, 1024);
    }
    fclose($fp);
}