<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/29/2016
 * Time: 4:39 PM
 */

namespace Spider\Test;

use Zend\Cache\StorageFactory;

class RedisService {

    private $redis = null;

    public function __construct($config) {
        $this->redis = StorageFactory::factory($config);
        var_dump(get_class($this->redis));exit;
    }

    public function getRedis() {
        return $this->redis;
    }
}