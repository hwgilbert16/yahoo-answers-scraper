<?php

require 'inc/scrape_page2.php';
//require 'inc/simple_html_dom.php';
require __DIR__ . '/vendor/autoload.php';
use simplehtmldom\HtmlWeb;

$url = 'https://answers.yahoo.com/question/index?qid=20210416002555AAm4gtK';

$parser = new HtmlWeb();
$inst = new scrape_page2($url, $parser);
$array = $inst->scrape_page();
var_dump($array);