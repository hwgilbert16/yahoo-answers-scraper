<?php

require 'inc/simple_html_dom.php';

$html = file_get_html('https://answers.yahoo.com/question/index?qid=20210416002555AAm4gtK');

$questionBody = "";
$questionTitle = "";
$questionNumAnswers = "";
$replyBodies = [];
$replyAuthors = [];

/* scrapes the asked question */

// question body
foreach($html->find('div[class^=Question__contentWrapper___lkV5T]') as $questionBody) {
    $questionBody = $questionBody->find('p', 0);
    $questionBody = $questionBody->innertext;
    $questionBody = str_replace("&#x27;", "'", $questionBody);
}

// question title
foreach($html->find('h1[class=Question__title___1Wgtn]') as $questionTitle) {
    $questionTitle = $questionTitle->innertext;
    $questionTitle = str_replace("&#x27;", "'", $questionTitle);
}

/* reply scraping */

// number of answers to question
foreach($html->find('div[class=AnswersList__listHeader___35PrW]') as $questionNumAnswers) {
    $questionNumAnswers = $questionNumAnswers->find('h3', 0);
    $questionNumAnswers = $questionNumAnswers->innertext;
    $questionNumAnswers = (int) filter_var($questionNumAnswers, FILTER_SANITIZE_NUMBER_INT);
}

// reply bodies
$replies = $html->find('ul[class=AnswersList__answersList___2ikkB]', 0);
foreach ($reply = $replies->find("li") as $reply) {
    foreach($reply->find('div[class^=ExpandableContent__content___2Iw4v]') as $replyBody) {
        $replyBody = $replyBody->plaintext;
        $replyBody = str_replace("&#x27;", "'", $replyBody);
        $replyBodies[] = $replyBody;
    }
}

// reply authors

foreach ($reply = $replies->find("li") as $reply) {
    foreach($reply->find('a[class=UserProfile__userName___1d1RW]') as $replyAuthor) {
        $replyAuthor = $replyAuthor->plaintext;
        $replyAuthors[] = $replyAuthor;
    }
}

var_dump($replyAuthors);