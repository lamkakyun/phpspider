<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 10:47 AM
 */
require_once '../vendor/autoload.php';

$worker = new \Workerman\Worker('tcp://0.0.0.0:8585');
$worker->count = 4;
$worker->onWorkerStart = function($worker)
{
    // 只在id编号为0的进程上设置定时器，其它1、2、3号进程不设置定时器
    if($worker->id === 0)
    {
        \Workerman\Lib\Timer::add(1, function(){
            echo "4个worker进程，只在0号进程设置定时器\n"; // 每隔一秒输出
        });
    }
};
// 运行worker
\Workerman\Worker::runAll();
