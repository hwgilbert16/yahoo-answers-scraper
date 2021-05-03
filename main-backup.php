<?php

$questionLinks[] = $crawler->getLinks($url);
var_dump($questionLinks);

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
    }

    $newLinkKey = count($questionLinks);
    $newLink = $questionLinks[$newLinkKey - 1];
    $crawler = new crawler($newLink, $parser);

    unset($questionLinks);

    $questionLinks[] = $crawler->getLinks();
    var_dump($questionLinks);
}

//var_dump($initialPageScraped);

