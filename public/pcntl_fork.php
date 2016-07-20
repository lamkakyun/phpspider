<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-21
 * Time: 上午12:14
 */

$parentPid = getmypid();
$pid = pcntl_fork();

if ($pid == -1) {
    die('fork failed!');
} elseif ($pid == 0) {
    $mypid = getmypid();
    echo 'I am child process. My PID is ' . $mypid . ' and my father\'s PID is ' . $parentPid . PHP_EOL;
    pcntl_exec('/bin/ls', ['-l']);
} else {
    pcntl_wait($status);
    echo 'Oh my god! I am a father now! My child\'s PID is ' . $pid . ' and mine is ' . $parentPid . PHP_EOL;
}