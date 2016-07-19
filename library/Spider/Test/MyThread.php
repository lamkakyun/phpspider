<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/19/2016
 * Time: 11:39 AM
 */

namespace Spider\Test;

/**
 * 使用pthreads 扩展实现线程
 * Class MyThread
 * @package Spider\Test
 */
class MyThread extends \Thread
{

    public function run()
    {
        for ($i = 0; $i < 10; $i ++) {
            echo MyThread::getCurrentThreadId() . "\n";
//            $i = \Zend\Math\Rand::getInteger(0,2);
            $second = rand(0,2);
            sleep($second);
        }
    }
}