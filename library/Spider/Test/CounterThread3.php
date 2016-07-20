<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/20/2016
 * Time: 11:23 AM
 */
namespace Spider\Test;

class CounterThread3 extends \Thread
{
    protected $shmid;
    protected $shmkey;

    public function __construct($shmid, $shmkey)
    {
        $this->shmid = $shmid;
        $this->shmkey = $shmkey;
    }

    public function run()
    {
        $this->synchronized(function ($thread) {
            echo $thread->getCurrentThreadId() . "  start\n";
            sleep(rand(0,5));
            echo $thread->getCurrentThreadId() . "  end\n";
        }, $this);

//        $counter = shm_get_var($this->shmid, $this->shmkey);
//        $counter ++;
//        shm_put_var($this->shmid, $this->shmkey, $counter);
//        printf("Thread #%lu says: %s\n", $this->getThreadId(),$counter);
    }
}