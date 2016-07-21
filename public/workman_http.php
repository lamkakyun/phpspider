<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/21/2016
 * Time: 11:52 AM
 */
require_once "../vendor/autoload.php";

$http_worker = new \Workerman\Worker('http://0.0.0.0:8080');

$http_worker->count = 4;

$http_worker->onMessage = function($connection, $data) {
//    $connection->send($data);
    $connection->send("hello world");
};

$http_worker->runAll();