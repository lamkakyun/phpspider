<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/19/2016
 * Time: 11:39 AM
 */

namespace Spider\Test;

class MyThread2 extends \Thread
{

    // 线程同步，只能有一个线程进入synchronized 代码块 (但是这样，很明显的失败了)(成功了，因为必须加入join)
    public function run()
    {
        $this->synchronized(function($thread) {
            echo $thread->getThreadId() . " start ...\n";
            sleep(rand(0,5));
            echo $thread->getThreadId() . " end   ...\n";
        }, $this);
    }
}