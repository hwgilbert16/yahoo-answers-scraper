<?php

class scrape_page
{

    private $url;
    private $html;
    private $parser;
    private $attempts;
    private $allowedAttempts;

    public function __construct($url, $parser) {
        $this->url = $url;
        $this->parser = $parser;
        $this->html = $this->parser->load($url);
        while ($this->html == null) {
            sleep(1);
            $this->html = $this->parser->load($url);
            echo 'html null - scrapepage';
        }
        $this->allowedAttempts = 5;
        $this->attempts = 0;
    }

    public function scrape_page() {
        $questionBody = $this->getQuestionBody();
        $questionTitle = $this->getQuestionTitle();
        $numAnswers = $this->getNumAnswers();
        $replyBodies = $this->getReplyBodies();
        $replyAuthors = $this->getReplyAuthors();

        $this->return_value["questionBody"] = $questionBody;
        $this->return_value["questionTitle"] = $questionTitle;
        $this->return_value["numAnswers"] = $numAnswers;
        $this->return_value["replyBodies"] = $replyBodies;
        $this->return_value["replyAuthors"] = $replyAuthors;
        $this->return_value["id"] = substr($this->url, 45);

        return $this->return_value;

    }

    private function getQuestionBody() {;
        $getQuestionBody = $this->html->find('div[class^=Question__contentWrapper___lkV5T]');
        if ($getQuestionBody == null || empty($getQuestionBody)) {
            return "";
        } else {
            foreach($getQuestionBody as $questionBody) {
                $questionBody = $questionBody->find('p', 0);
                $questionBody = $questionBody->innertext;
                return str_replace("&#x27;", "'", $questionBody);
            }
        }
    }

    private function getQuestionTitle() {
        foreach($this->html->find('h1[class=Question__title___1Wgtn]') as $questionTitle) {
            $questionTitle = $questionTitle->innertext;
            return str_replace("&#x27;", "'", $questionTitle);
        }
    }

    private function getNumAnswers() {
        $questionNumAnswers = $this->html->find('h3[class=ColoredHeadline__headline___el6Ni]', 0);
        if ($questionNumAnswers == null) {
            return 0;
        } else {
            //$questionNumAnswers = $questionNumAnswers->find('h3', 0);
            $questionNumAnswers = $questionNumAnswers->innertext;
            return filter_var($questionNumAnswers, FILTER_SANITIZE_NUMBER_INT);
        }

        /*foreach($this->html->find('div[class=AnswersList__listHeader___35PrW]') as $questionNumAnswers) {
            $questionNumAnswers = $questionNumAnswers->find('h3', 0);
            $questionNumAnswers = $questionNumAnswers->innertext;
            return filter_var($questionNumAnswers, FILTER_SANITIZE_NUMBER_INT);
        }*/
    }

    private function getReplyBodies() {
        $replies = $this->html->find('ul[class=AnswersList__answersList___2ikkB]', 0);
        if ($replies == null) {
            return "";
        } else {
            foreach ($reply = $replies->find("li") as $reply) {
                //sleep(3);
                foreach($reply->find('div[class^=ExpandableContent__content___2Iw4v]') as $replyBody) {
                    //sleep(3);
                    $replyBody = $replyBody->plaintext;
                    $replyBody = str_replace("&#x27;", "'", $replyBody);
                    $replyBodies[] = $replyBody;
                }
            }
            return $replyBodies;
        }
    }

    private function getReplyAuthors() {
        $replies = $this->html->find('ul[class=AnswersList__answersList___2ikkB]', 0);
        if ($replies == null) {
            return 0;
        } else {
            $replyAuthors = [];
            foreach ($reply = $replies->find("li") as $reply) {
                foreach($reply->find('a[class=UserProfile__userName___1d1RW]') as $replyAuthor) {
                    $replyAuthor = $replyAuthor->plaintext;
                    $replyAuthors[] = $replyAuthor;
                }

                foreach($reply->find('div[class=UserProfile__userName___1d1RW]') as $replyAuthor) {
                    $replyAuthor = $replyAuthor->plaintext;
                    $replyAuthors[] = $replyAuthor;
                }
            }
            return $replyAuthors;
        }
    }
}