<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-20
 * Time: 下午9:38
 */

class CounterThread extends Thread
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
            $thread->wait();
        }, $this);
        $counter = shm_get_var($this->shmid, $this->shmkey);
        $counter++;
        shm_put_var($this->shmid, $this->shmkey, $counter);
        printf("Thread #%lu says: %s\n", $this->getThreadId(), $counter);
    }
}

$tmp = tempnam(__FILE__, 'PHP');
$key = ftok($tmp, 'a');

$shmid = shm_attach($key);
$counter = 0;
$shmkey = 1;
shm_put_var($shmid, $shmkey, $counter);

for ($i = 0; $i < 100; $i++) {
    $threads[] = new CounterThread($shmid, $shmkey);
}

for ($i = 0; $i < 100; $i++) {
    $threads[$i]->start();

}

for ($i = 0; $i < 100; $i++) {
    $threads[$i]->synchronized(function ($thread) {
        $thread->notify();
    }, $threads[$i]);
}

for ($i = 0; $i < 100; $i++) {
    $threads[$i]->join();
}
shm_remove($shmid);
shm_detach($shmid);