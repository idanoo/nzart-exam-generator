<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12/30/16
 * Time: 2:09 PM
 */

class Answer extends DataItem
{

    public static function _getClass()
    {
        return "Answer";
    }

    public static function _getType()
    {
        return "answer";
    }

    public function getAnswer()
    {
        return $this->answerdata_content;
    }

    public function isCorrect()
    {
        return $this->answerdata_correct;
    }

    public static function checkIfCorrect($answer)
    {

    }
}