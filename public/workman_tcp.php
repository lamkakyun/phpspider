<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-21
 * Time: 下午10:03
 */

require '../vendor/autoload.php';

$tcp_server = new \Workerman\Worker('tcp://0.0.0.0:8080');

$tcp_server->count = 4;
$tcp_server->onMessage = function($conn, $data) {
    $conn->send("hello " . $data);
};

$tcp_server->runAll();