<?php

namespace Application\Controller;

use Spider\Test\PublicService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TestController extends AbstractActionController
{

    /**
     * @desc http://phpspider.test.com/application/test/curl
     * @access public
     * @author lamkakyun
     * @return void
     */
    public function curlAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $model = new ViewModel();
            $model->setTerminal(true);

            return $model;
        }

        $protocol = $this->getRequest()->getPost('protocol');
        $method = $this->getRequest()->getPost('method');
        $url = $this->getRequest()->getPost('url');
        $_args = $this->getRequest()->getPost('agrs');
        $args = [];
        foreach ($_args as $value)
        {
            if (empty($value)) continue;
            list($k, $v) = explode("=", $value);
            $args[$k] = $v;
        }

//        $ret = PublicService::call_api($url, $args, $method);


        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Connection: Keep-Alive',
            'Keep-Alive: 300'
        ));

        if (strtoupper($method) == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
        }

        if (strtoupper($protocol) == 'HTTPS')
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }
        $result = curl_exec($ch);

        echo '<pre>';
//        var_export($protocol);
//        var_export($method);
//        var_export($url);
        var_export($result);
        exit;
    }
}