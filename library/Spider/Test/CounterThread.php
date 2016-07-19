<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-20
 * Time: 上午12:50
 */

namespace Spider\Test;

class CounterThread extends \Thread
{

    protected $mutex;
    protected $handle;

    public function __construct($mutex = null)
    {
        $this->mutex = $mutex;
        $this->handle = fopen('/tmp/phpspider/counter.txt', 'w+');
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    public function run() {
        if ($this->mutex) $locked = \Mutex::lock($this->mutex);

        $counter = intval(fgets($this->handle));
        $counter++;
        rewind($this->handle);
        fputs($this->handle, $counter);
        printf("Thread #%lu says: %s\n", $this->getThreadId(),$counter);

        if ($this->mutex) \Mutex::unlock($this->mutex);

    }
}