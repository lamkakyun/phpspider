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


    public static function rolling_curl($urls)
    {
        $queue = curl_multi_init();
        $map = [];

        foreach ($urls as $url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 多线程场景下应该设置CURLOPT_NOSIGNAL选项，因为在解析DNS出现超时的时候将会发生“糟糕”的情况
            // 在多线程处理场景下使用超时选项时，会忽略signals对应的处理函数
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

            curl_multi_add_handle($queue, $ch);
            $map[(string)$ch] = $url; // key 是独一无二，有资源转换成的字符串
        }

        $res = [];
        $active = false;
        do {
            // 让所有curl请求运行起来
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM);

            while ($done = curl_multi_info_read($queue)) { // 查询批处理句柄是否单独的传输线程中有消息或信息返回，直到这时没有更多信息返回时，FALSE 被当作一个信号返回
                $info= curl_getinfo($done['handle']);
                $error= curl_error($done['handle']);
                $results= curl_multi_getcontent($done['handle']);
                $res[$map[(string)$done['handle']]] = compact('info','error', 'results');

                curl_multi_remove_handle($queue,$done['handle']);
                curl_close($done['handle']);
            }

            if ($active) curl_multi_select($queue, 0.5);

        } while ($active);

        curl_multi_close($queue);

        return $res;
    }
}