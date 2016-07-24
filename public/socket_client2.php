<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-22
 * Time: 下午9:36
 */

$host = '127.0.0.1';
$port = 8080;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('create socket failed ' . socket_strerror(socket_last_error($sock)));

$conn = socket_connect($sock, $host, $port) or die('connect server failed ' . socket_strerror(socket_last_error($sock)));

socket_write($sock, "hello server") or die('write failed! ' . socket_strerror(socket_last_error($sock)));

while ($buff = socket_read($sock, 1024, PHP_NORMAL_READ)) {
    echo("Response was:" . $buff . "\n");
}
socket_close($sock);