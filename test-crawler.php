<?php

require 'inc/scrape_page.php';
require __DIR__ . '/vendor/autoload.php';
require 'inc/crawler.php';
use simplehtmldom\HtmlWeb;

$url = 'https://answers.yahoo.com/question/index?qid=20210416002555AAm4gtK';
$parser = new HtmlWeb();
$html = $parser->load($url);

$instance = new scrape_page($url, $parser);
$instance2 = new crawler($url, $parser);

$scrapedLinks = [];
$scrapedPage = [];

$scrapedData = $instance->scrape_page();

$scrapedLinks[] = $instance2->getLinks($url);

for ($counter = 0; $counter < count($scrapedLinks[0]); $counter++) {
    $link = $scrapedLinks[0][$counter];

    $page = $parser->load($link);
    $scraper = new scrape_page($link, $parser);

    $scrapedPage[] = $scraper->scrape_page();
    echo "success". $counter;
}

//

var_dump($scrapedPage[1]);