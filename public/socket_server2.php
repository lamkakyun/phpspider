<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-22
 * Time: 下午9:26
 */

set_time_limit(0);
$host = '0.0.0.0';
$port = 8080;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('create socket failed!');

socket_set_block($sock) or die('set socket block failed:' . socket_strerror(socket_last_error($sock)));

$result = socket_bind($sock, $host, $port) or die('socket bind err:' . socket_strerror(socket_last_error($sock)));

$result = socket_listen($sock, 4) or die('socket listen failed: ' . socket_strerror(socket_last_error($sock)));

echo "OK\nBinding the socket on $address:$port ... ";
echo "OK\nNow ready to accept connections.\nListening on the socket ... \n";

do {
    $client = socket_accept($sock) or die('socket accept failed： ' . socket_strerror(socket_last_error($sock)));

    echo "Read client data \n";

    $buf = socket_read($client, 8192);
    echo "Received msg: $buf   \n";

    $msg = "welcome \n";

    socket_write($client, $msg, strlen($msg)) or die('socket write failed! ' . socket_strerror(socket_last_error($sock)));
} while(true);

socket_close($sock);