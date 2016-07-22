<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/22/2016
 * Time: 2:03 PM
 */

$base = event_base_new();
$event = event_new();

event_set($event, 0, EV_TIMEOUT, function() {
    echo "function called!\n";
});

event_base_set($event, $base);

event_add($event, 5000000);
event_base_loop($base);