<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/20/2016
 * Time: 10:43 AM
 */

namespace Spider\Test;

class CounterThread2 extends \Thread
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
        $counter = shm_get_var($this->shmid, $this->shmkey);
        $counter ++;
        sleep(1);
        shm_put_var($this->shmid, $this->shmkey, $counter);
        printf("Thread #%lu says: %s\n", $this->getThreadId(), $counter);
    }
}