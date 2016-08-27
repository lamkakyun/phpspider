<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-8-27
 * Time: 下午4:55
 */

namespace Admin\Controller;


use Zend\View\Model\ViewModel;

class PublicController extends CommonController
{

    public function loginAction() {
        $model = new ViewModel();
        $model->setTerminal(true); // 不设置模板
        return $model;
    }
}