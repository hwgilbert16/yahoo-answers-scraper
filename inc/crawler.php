<?php

class crawler
{
    private $url;
    private $parser;
    private $html;

    public function __construct($url, $parser)
    {
        $this->url = $url;
        $this->parser = $parser;
        $this->html = $this->parser->load($url);
        while ($this->html == null) {
            sleep(1);
            $this->html = $this->parser->load($url);
            var_dump($url);
            echo 'html null - crawler';
        }
    }

    public function getLinks($url) {
        $scrapedLinks = [];

        foreach ($this->html->find('a[class^=QuestionListResponsiveWithSide__questionTitle___nNlCE]') as $individualLinks) {
            $scrapedLinks[] = 'https://answers.yahoo.com'. $individualLinks->href;
        }

        return $scrapedLinks;
    }


}