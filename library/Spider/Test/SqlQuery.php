<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-20
 * Time: 上午12:40
 */

namespace Spider\Test;

class SqlQuery extends \Stackable
{

    protected $sql;

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function run()
    {
        $dbh = $this->worker->getConnection();
        $row = $dbh->query($this->sql);
        while ($member = $row->fetch(\PDO::FETCH_ASSOC)) {
            print_r($member);
        }
    }
}