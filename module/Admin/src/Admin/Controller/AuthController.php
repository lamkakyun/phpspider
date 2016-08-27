<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-8-27
 * Time: 下午2:46
 */

namespace Admin\Controller;

use Admin\Controller\CommonController;


/**
 * 身份验证控制器
 * Class AuthController
 * @package Admin
 */
class AuthController extends CommonController {


    public function __construct() {
        $this->checkLogin();
    }


    public function checkLogin() {
        
    }

}