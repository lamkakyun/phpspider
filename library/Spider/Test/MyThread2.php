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

    // 线程同步，只能有一个线程进入synchronized 代码块 (但是这样，很明显的失败了)
    public function run()
    {
        $this->lock();
        echo $this->getCurrentThreadId() . " start ...\n";
        sleep(rand(0,3));
        echo $this->getCurrentThreadId() . " end   ...\n";
        $this->unlock();
    }
}