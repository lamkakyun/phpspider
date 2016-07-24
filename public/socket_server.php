<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 2:26 PM
 */

// 可以使用telnet 进行连接 telnet 127.0.0.1 8080
//$socket = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr);
//if (!$socket) {
//    echo "$errstr ($errno)<br />\n";
//} else {
//    while ($conn = stream_socket_accept($socket)) {
//        fwrite($conn, "the local time is " . date('Y-m-d H:i:s') . "\n");
//        fclose($conn);
//    }
//    fclose($socket);
//}


// 多进程模式以实现 连接多个client
//$socket = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr);
//while (true) {
//    $conn = stream_socket_accept($socket);
//    if (pcntl_fork() == 0) {
//        $ret = 1;
//        while ($conn) {
//            $request = fread($conn, 1024);
//            if ($request == "bye\r\n" or !$ret) {
//                echo "disconnet\n";
//                fclose($conn);
//                unset($conn);
//                break;
//            }
//            echo "read: $request\n";
//            $response = "server: default_response\n";
//            $ret = fwrite($conn, $response);
//        }
//    }
//}