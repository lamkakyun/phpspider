<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/21/2016
 * Time: 10:36 AM
 */

$child = [];
$parentPid = getmypid();

for($i = 0; $i < 10; $i++) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('fork err!');
    } elseif ($pid) {
        echo "fork! i am parent! pid = $parentPid\n";
        $child[] = $pid;
    } else {
        $mypid = getmypid();
        echo "i am child $i pid = $mypid\n";
        sleep($i+1);
        exit();
    }
}

while(count($child)) {
    foreach($child as $key => $pid) {
        // 发生错误时返回-1,如果提供了 WNOHANG作为option（wait3可用的系统）并且没有可用子进程时返回0。
        // WNOHANG	如果没有子进程退出立刻返回。
        $res = pcntl_waitpid($pid, $status, WNOHANG);

        if($res == -1 || $res > 0) {
            unset($child[$key]);
        }
    }
    sleep(1);
}