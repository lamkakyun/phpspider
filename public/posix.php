<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 16-7-21
 * Time: 上午1:08
 */
var_dump(posix_getcwd());
var_dump(posix_getuid());
var_dump(posix_geteuid());
var_dump(posix_getgid());
var_dump(posix_getpid());
var_dump(posix_getpgrp());
var_dump(posix_ttyname(STDOUT));
var_dump(posix_uname());
var_dump(posix_times());
var_dump(posix_getrlimit());
var_dump(posix_getpwnam('jet'));
var_dump(posix_getlogin());
var_dump(posix_getgroups());
var_dump(posix_ctermid());