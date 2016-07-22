<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 2:26 PM
 */

// 可以使用telnet 进行连接 telnet 127.0.0.1 8080
$socket = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr);
if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    while ($conn = stream_socket_accept($socket)) {
        fwrite($conn, "the local time is " . date('Y-m-d H:i:s') . "\n");
        fclose($conn);
    }
    fclose($socket);
}