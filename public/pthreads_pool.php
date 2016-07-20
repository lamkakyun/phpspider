<?php

/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-21
 * Time: 上午12:20
 */
class Update extends Thread
{
    public $running = false;
    public $row = array();
    public $sql;

    public function __construct($row)
    {
        $this->row = $row;
        $this->sql = null;
    }

    public function run()
    {
        if (strlen($this->row['bankno'] > 100)) {}
    }
}