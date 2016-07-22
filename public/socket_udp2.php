<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 5:58 PM
 */

$fp = stream_socket_client("udp://127.0.0.1:8080", $errno, $errstr);
if (!$fp) {
    echo "ERROR: $errno - $errstr<br />\n";
} else {
    fwrite($fp, "\n");
    echo fread($fp, 26);
    fclose($fp);
}
