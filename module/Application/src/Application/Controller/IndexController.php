<?php
/**
 * Zend Framework (http://framework.zend.com/)
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Text\Figlet\Figlet;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Math\Rand;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * 命令行的帮助列表
     * @return string
     */
    public function helpAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $figlet = new Figlet();

        return $figlet->render("No Help List!!");
    }

    /**
     * 文档教程默认的控制器
     * @return string
     */
    public function resetpasswordAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $userEmail = $request->getParam('userEmail');
        $verbose = $request->getParam('verbose') || $request->getParam('v');

        $newPassword = Rand::getString(16);

        if (!$verbose) {
            return "Done! $userEmail has received an email with his new password.\n";
        } else {
            return "Done! New password for user $userEmail is '$newPassword'. It has also been emailed to him. \n";
        }
    }


    /**
     * 使用requests 库 做一个小小的测试
     */
    public function testAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $url = "https://www.zhihu.com/question/48235810";
        $request = \Requests::get($url);
        echo $request->body;
    }
}
