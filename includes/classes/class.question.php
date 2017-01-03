<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12/30/16
 * Time: 2:09 PM
 */

class Question extends DataItem {

    public static function _getClass()
    {
        return "Question";
    }

    public static function _getType()
    {
        return "question";
    }

    public static function getQuestions($count)
    {
        if($count == "60") return self::getExamQuestions();
        $questions = self::getAllWhere(false, "order by rand()", false, $count);
        foreach ($questions as $q) {
            $q->answers = $q->getAnswers();
            shuffle($q->answers);
        }
        return $questions;
    }

    public static function getExamQuestions()
    {
        $questions = [];
        $i=1;
        while ($i<=30) {
            $limit = parent::count("SELECT COUNT(*)/10 FROM question WHERE FLOOR(questiondata_number) = ".$i);
            $questions = array_merge($questions, self::getAllWhere("FLOOR(questiondata_number) = ".$i, "order by rand()", false, intval($limit)));
            $i++;
        }
        foreach ($questions as $q) {
            $q->answers = $q->getAnswers();
            shuffle($q->answers);
        }
        return $questions;

    }

    public function getQuestion()
    {
        return $this->questiondata_content;
    }

    public function getAnswers()
    {
        return Answer::getAllWhere("answerdata_question = ".$this->getId());
    }

    public function getCorrectAnswer()
    {
        return Answer::getWhere("answerdata_question = ".$this->getId()." and answerdata_correct = 1");
    }

    public function getImage()
    {
        return $this->questiondata_image;
    }

    public function getCountFromNumber()
    {

    }
}