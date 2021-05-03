<?php

/**
 * @var string $connection
 */

require 'inc/conn.php';
require 'inc/crawler.php';
require 'inc/scrape_page.php';

use simplehtmldom\HtmlWeb;

require __DIR__ . '/vendor/autoload.php';

/* --- */

$url = 'https://answers.yahoo.com/question/index?qid=20210419154803AAq2UGC';
$parser = new HtmlWeb();

$scraper = new scrape_page($url, $parser);
$crawler = new crawler($url, $parser);

/* --- */

$initialPageDOM = $parser->load($url);
$initialPageScraped = $scraper->scrape_page();

/* --- */

$questionBody = $initialPageScraped['questionBody'];
$questionTitle = $initialPageScraped['questionTitle'];
$numAnswers = $initialPageScraped['numAnswers'];
$questionId = $initialPageScraped['id'];

$query = $connection->prepare("INSERT INTO questions (question_body, question_title, question_number_answers, question_id) VALUES (?, ?, ?, ?)");

$query->bind_param('ssss', $questionBody, $questionTitle, $numAnswers, $questionId);
$query->execute();

/* --- */

if ($numAnswers !== 0) {
    for ($counter = 0; $counter < $numAnswers; $counter++) {
        $replyBody = $initialPageScraped['replyBodies'][$counter];
        $replyAuthor = $initialPageScraped['replyAuthors'][$counter];

        $query = $connection->prepare("INSERT INTO replies (reply_body, reply_author, question_id) VALUES (?, ?, ?)");
        $query->bind_param("sss", $replyBody, $replyAuthor, $questionId);
        $query->execute();
    }
}

/* --- */

$questionLinks[] = $crawler->getLinks($url);

while (1) {
    for ($counter = 0; $counter < count($questionLinks[0]); $counter++) {

        $link = $questionLinks[0][$counter];
        $DOM = $parser->load($link);

        $scraper = new scrape_page($link, $parser);
        $scrapedPage = $scraper->scrape_page();

        /* --- */

        $questionBody = $scrapedPage['questionBody'];
        $questionTitle = $scrapedPage['questionTitle'];
        $numAnswers = $scrapedPage['numAnswers'];
        $questionId = $scrapedPage['id'];

        $query = $connection->prepare("INSERT INTO questions (question_body, question_title, question_number_answers, question_id) VALUES (?, ?, ?, ?)");

        $query->bind_param('ssss', $questionBody, $questionTitle, $numAnswers, $questionId);
        $query->execute();

        /* --- */

        if ($numAnswers !== 0) {
            for ($counter1 = 0; $counter1 < $numAnswers; $counter1++) {
                if ($counter1 > count($scrapedPage['replyBodies']) - 1) {
                    break;
                }
                $replyBody = $scrapedPage['replyBodies'][$counter1];
                $replyAuthor = $scrapedPage['replyAuthors'][$counter1];

                $query = $connection->prepare("INSERT INTO replies (reply_body, reply_author, question_id) VALUES (?, ?, ?)");
                $query->bind_param("sss", $replyBody, $replyAuthor, $questionId);
                $query->execute();
            }
        }

        echo $questionTitle. " uploaded successfully", PHP_EOL;
        var_dump($counter);
    }

    /* --- */

    $questionLinksTemp = [];

    for ($counter = 0; $counter < count($questionLinks[0]); $counter++) {
        $id = substr($questionLinks[0][$counter], 45);
        $query = $connection->query("SELECT `question_id` FROM `questions` WHERE `question_id`='".$id."'");

        if ($query->num_rows !== 0) {
            continue;
        }

        $questionLinksTemp[] = $questionLinks[0][$counter];
    }

    $questionLinks[] = $questionLinksTemp;

    $newLink = $questionLinksTemp[1];
    $crawler = new crawler($newLink, $parser);

    unset($questionLinks);
    $questionLinks[] = $crawler->getLinks($newLink);

    var_dump($questionLinks);

}
