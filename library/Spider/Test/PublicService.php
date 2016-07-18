<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-18
 * Time: 下午9:59
 */

namespace Spider\Test;

class PublicService
{

    /**
     * 使用curl请求地址
     * @param $url
     * @param $data
     * @param string $method
     */
    public static function call_api($url, $data = [], $method = 'POST')
    {
        $cookie_file = '/tmp/phpspider/cookie.txt';

        if (!file_exists($cookie_file) || !is_writable($cookie_file)) {
            echo 'Cookie file missing or not writable.';
            exit;
        }

        if (!extension_loaded('curl')) {
            echo "You need to load/activate the curl extension.";
            exit;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true); // 返回头部
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)'); // 假冒浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //存储cookies
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file); //存储cookies
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 60s 请求时间

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Connection: Keep-Alive',
            'Keep-Alive: 300'
        ));

        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, true); // 使用POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // 需要POST的数据
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $ret = self::error(curl_error($ch));
        } else {
            $ret = self::success('call api success', $result);
        }

//        $info = curl_getinfo($ch);
//        var_dump($info);

        curl_close($ch);

        return $ret;
    }


    public static function json($data)
    {
        return json_encode($data);
    }

    public static function error($msg)
    {
        return ['success' => false, 'msg' => $msg];
    }

    public static function success($msg, $data = null)
    {
        $ret = ['success' => true, 'msg' => $msg];
        if ($data) {
            $ret['data'] = $data;
        }

        return $ret;
    }
}