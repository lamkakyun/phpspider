<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-22
 * Time: 上午12:37
 */

require_once '../vendor/autoload.php';
require_once '../vendor/workerman/workerman/Protocols/JsonNL.php';

$json_worker = new \Workerman\Worker('JsonNL://0.0.0.0:8080');
$json_worker->count = 4;
$json_worker->onMessage = function($conn, $data) {
    $conn->send("you said: " . \GuzzleHttp\json_encode($data));
};

\Workerman\Worker::runAll();