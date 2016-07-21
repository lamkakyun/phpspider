<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/21/2016
 * Time: 11:33 AM
 */
function newChild($func_name) {
    echo "enter newChild\n";
    $args = func_get_args();
    unset($args[0]);
    $pid =  pcntl_fork();
    if ($pid == 0) {
        function_exists($func_name) and exit(call_user_func_array($func_name, $args)) or exit(-1);
    } else if($pid == -1) {
        echo "Couldn't create child process";
    } else {
        return $pid;
    }
}

function on_timer() {
    echo "timer called\n";
}

/**
 * @param $func string, function name
 * @param $timeouts int, microtimes for time delay
 */
function timer($func, $timeouts){

    echo "enter timer\n";
    $base = event_base_new();
    $event = event_new();

    event_set($event, 0, EV_TIMEOUT, $func);
    event_base_set($event, $base);
    event_add($event, $timeouts);

    event_base_loop($base);
}

$pid = newChild("timer", "on_timer", 5000000);

if ($pid > 0) {
    echo "master process exit\n";
}