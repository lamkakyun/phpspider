<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-17
 * Time: 下午2:30
 */

namespace Spider\Test;

use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

class MailService
{

    protected $transport;

    public function __construct($config)
    {
        $smtp_options = new SmtpOptions();
        $smtp_options->setHost('smtp.qq.com')
            ->setConnectionClass('login')
            ->setName('smtp.qq.com')
            ->setConnectionConfig($config);

        $this->transport = new Smtp($smtp_options);
    }

    public function send($content)
    {
        $message = new \Zend\Mail\Message();
        $message->setBody('This ia the text of email.');
        $message->setFrom('756431672@qq.com', 'lamkakyun');
        $message->addTo('lamkakyun@gmail.com', 'jet');
//        $message->setSubject('TestSubject, hi i am jet. nice to meet you!');
        $message->setSubject($content);

        $this->transport->send($message);
    }
}