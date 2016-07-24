<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-22
 * Time: 下午9:53
 */

$host = '127.0.0.1';
$port = '8080';

$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

$msg = "hello";

$len = strlen($msg);

socket_sendto($sock, $msg, $len, 0, $host, $port);

socket_close($sock);