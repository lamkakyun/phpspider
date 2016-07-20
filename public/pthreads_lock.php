<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/20/2016
 * Time: 5:49 PM
 */
$tmp = tempnam(__FILE__, 'PHP');
$key = ftok($tmp, 'a');

$shmid = shm_attach($key);
$counter = 0;
shm_put_var( $shmid, 1, $counter );

class CounterThread extends Thread {
    public function __construct($shmid){
        $this->shmid = $shmid;
    }
    public function run() {

        $this->lock(); // 主要用于锁住读写
        echo $this->getCurrentThreadId() . " start \n";
        sleep(rand(0,3));
        $counter = shm_get_var( $this->shmid, 1 );
        $counter++;
        shm_put_var( $this->shmid, 1, $counter );

        printf("Thread #%lu says: %s\n", $this->getThreadId(),$counter);
        echo $this->getCurrentThreadId() . " end \n\n";
        $this->unlock();
    }
}

for ($i=0;$i<100;$i++){
    $threads[] = new CounterThread($shmid);
}
for ($i=0;$i<100;$i++){
    $threads[$i]->start();
}

//for ($i=0;$i<100;$i++){
//    $threads[$i]->join();
//}
//echo "bingo\n";
shm_remove( $shmid );
shm_detach( $shmid );
?>