<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-20
 * Time: 上午12:37
 */

namespace Spider\Test;

class ExampleWorker extends \Worker {

    public static $dbh;



    public function run() {
        self::$dbh = new \PDO('mysql:host=127.0.0.1;dbname=test', 'jet', '123');
    }

    public function getConnection() {
        return self::$dbh;
    }
}