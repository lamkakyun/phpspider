<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-21
 * Time: 下午10:24
 */

require_once '../vendor/autoload.php';

$global_uid = 0;

// 每建立一个连接，都是独立的
function handle_connection($conn) {
    global $global_uid;
    $conn->uid = ++$global_uid;
}

// 当客户端发送消息过来时，转发给所有人
function handle_message($connection, $data) {
    global $text_worker;
    foreach ($text_worker->connections as $conn) {
        $conn->send("user[$connection->uid] said: $data");
    }
}

// 当客户端断开时，广播给所有客户端
function handle_close($connection) {
    global $text_worker;
    foreach ($text_worker->connections as $conn) {
        $conn->send("user[{$connection->uid}] logout");
    }
}


$text_worker = new \Workerman\Worker("text://0.0.0.0:8080");

$text_worker->count = 1;

$text_worker->onConnect = 'handle_connection';
$text_worker->onMessage = 'handle_message';
$text_worker->onClose = 'handle_close';

$text_worker->runAll();