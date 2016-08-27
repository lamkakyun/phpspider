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

    /**
     * 登陆页面
     * @return ViewModel
     */
    public function loginAction()
    {
        $model = new ViewModel();
        $model->setTerminal(true); // 不设置模板
        return $model;
    }


    /**
     * 登陆操作
     */
    public function doLoginAction()
    {

    }


    /**
     * 注册页面
     * @return ViewModel
     */
    public function registerAction()
    {
        $model = new ViewModel();
        $model->setTerminal(true);
        return $model;
    }


    /**
     * 注册操作
     */
    public function doRegisterAction()
    {

    }

}