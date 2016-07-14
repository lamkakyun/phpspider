<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 7/14/2016
 * Time: 5:31 PM
 */
namespace Application\Service;

use PHPHtmlParser\Dom;

class TestService {

    public function __construct() { }

    public function test1() {
        $url = "http://log.lamkakyun.com";
        $request = \Requests::get($url);
        $dom = new Dom();
        $dom->load($request->body);
//        echo $request->body;
//        echo $dom->find('title')[0];

        $collection = $dom->find('.post-title');
        $iterator = $collection->getIterator();
        echo "Iterating over: " . $iterator->count() . " values\n";

        while( $iterator->valid() )
        {
//            echo $iterator->key() . "=" . $iterator->current() . "\n";
//            echo $iterator->key() . "=" . $iterator->current()->innerHtml . "\n";
            echo $iterator->key() . "=" . $iterator->current()->innerHtml . "\n";
            $iterator->next();
        }
    }
}