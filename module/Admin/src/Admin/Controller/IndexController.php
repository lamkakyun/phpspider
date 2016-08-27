<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-8-27
 * Time: 下午2:18
 */

namespace Admin\Controller;


use Admin\Controller\AuthController;
use Zend\View\Model\ViewModel;

class IndexController extends AuthController
{


    /**
     * 系统后台管理页面首页
     */
    public function indexAction()
    {
        return new ViewModel();
    }


    public function infoAction()
    {
        echo '打印系统和架构信息';
        exit;
    }
}