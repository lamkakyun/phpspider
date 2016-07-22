<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 2:10 PM
 */

function print_line($fd, $events, $arg) {
    static $max_requests = 0;
    $max_requests ++;

    if ($max_requests == 10) {
        // 处理10吃事件请求后退出程序
        event_base_loopexit($arg[1]);
    }
    echo fgets($fd);
}

$base = event_base_new();
$event = event_new();
$fd = STDIN;
event_set($event, $fd, EV_READ | EV_PERSIST, 'print_line', array($event, $base));
event_base_set($event, $base);
event_add($event);
event_base_loop($base);