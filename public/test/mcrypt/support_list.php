<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/26/2016
 * Time: 3:16 PM
 */


/**
 * 使用openssl 而不是 mcrypt
 */

$cipher_list = mcrypt_list_algorithms();//mcrypt支持的加密算法列表
$mode_list = mcrypt_list_modes();   //mcrypt支持的加密模式列表

//print_r($cipher_list);
//print_r($mode_list);

var_dump(md5('123456'));
var_dump(hash('md5', '123456'));
