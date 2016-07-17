<?php

// php getopt.php -a --p=123
require_once '../vendor/autoload.php';


// declare that options may be given as -a, -b, or --p. The latter flag requires a parameter.
//try {
//    $opts = new \Zend\Console\Getopt('abp:');
////    $opts->parse();
//
//    var_dump($opts->getOption('a'));
//    var_dump($opts->getOption('b'));
//    var_dump($opts->getOption('p'));
//} catch (\Exception $e) {
//    echo 'exception:' . $e->getMessage();
//}

// 或者可以这样声明
//"=s" for a string parameter
//"=w" for a word parameter (a string containing no whitespace)
//"=i" for an integer parameter
$opts = new \Zend\Console\Getopt(
    array(
        'apple|a'    => 'apple option, with no parameter ...', // 可输入参数 -a or --apple
        'banana|b=i' => 'banana option, with required integer parameter ...', // 可输入 -b or --banana=12 , 可选参数必须是整数
        'pear|p-s'   => 'pear option, with optional string parameter ...' // 同上，--pear必须是字符串
    )
);

$opts->setHelp(array(
    'a' => 'apple option, with no parameter +++',
    'b' => 'banana option, with required integer parameter +++',
    'p' => 'pear option, with optional string parameter +++',
));

// 将现实sethelp的信息
echo $opts->getUsageMessage();

// 总结 Getopt类是用来做 命令行的，Console才是用在