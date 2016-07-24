<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-22
 * Time: 下午9:52
 */

$host = '0.0.0.0';
$port = '8080';

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    die ('create socket fail ' . socket_strerror(socket_last_error($socket)));
}

$ok = socket_bind($socket, $host, $port);
if (!$ok) die('socket bind fail ' . socket_strerror(socket_last_error($socket)));

while (true) {
    $form = "";
    $port = 0;
    socket_recvfrom($socket, $buf, 1024, 0, $form, $port);
    echo $buf;
    usleep(1000);
}