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

    public static function getQuestions($count = false)
    {
        $questions = static::getAllWhere(false, "order by rand()", false, $count);
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

    public function getImage()
    {
        return $this->questiondata_image;
    }
}