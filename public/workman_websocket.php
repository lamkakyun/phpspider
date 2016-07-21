<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/21/2016
 * Time: 11:52 AM
 */

require_once "../vendor/autoload.php";

$socket_server = new \Workerman\Worker("websocket://0.0.0.0:8080");
$socket_server->count = 4;
$socket_server->onMessage = function($conn, $data) {
    $conn->send('hello' . $data);
};

$socket_server->runAll();


// html 文件， chrome 使用 WebSocket
//<!DOCTYPE html>
//<html lang="en">
//<head>
//    <meta charset="UTF-8">
//    <title>web socket</title>
//</head>
//<body>
//
//</body>
//<script type="text/javascript">
//// 假设服务端ip为127.0.0.1
//ws = new WebSocket("ws://127.0.0.1:8080");
//ws.onopen = function() {
//    alert("连接成功");
//    ws.send('tom');
//    alert("给服务端发送一个字符串：tom");
//};
//ws.onmessage = function(e) {
//    alert("收到服务端的消息：" + e.data);
//};
//</script>
//</html>