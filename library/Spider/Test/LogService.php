<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-17
 * Time: 下午2:30
 */

namespace Spider\Test;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class LogService
{

    public function logConsole()
    {
        $writer = new Stream('php://output');
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info('console information message log!');
    }

    public function logFile($file = null)
    {
        if (!isset($file) or empty($file)) {
            $file = '/tmp/logs/phpspider.log';
        }
        if (!file_exists($file)) {
            $file_obj = new \SplFileInfo($file);
            if (!file_exists($file_obj->getPath())) mkdir($file_obj->getPath());
            touch($file);
        }
        $writer = new Stream($file);
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info('file information message log!');
    }
}