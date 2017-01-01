<?php

class Result extends DataItem {

    public static function _getClass()
    {
        return "Result";
    }

    public static function _getType()
    {
        return "result";
    }

    public function setResult($result)
    {
        $this->resultdata_result = json_encode($result);
    }

    public function getResult()
    {
        return json_decode($this->resultdata_result, true);
    }

    public function setScore($score)
    {
        $this->resultdata_score = json_encode($score);
    }

    public function getScore()
    {
        return json_decode($this->resultdata_score, true);
    }
    
    public function setUser($userId)
    {
        $this->resultdata_user = $userId;
    }

    public function getUser()
    {
        return $this->resultdata_user;
    }

    public function save()
    {
        $db = new db();
        $db->query("INSERT INTO result(result_time, resultdata_user, resultdata_result, resultdata_score)
                  VALUES(:qTime, :qUser, :qContent, :qScore)");
        $db->bind("qTime", time());
        $db->bind("qUser", $this->resultdata_user);
        $db->bind("qScore", $this->resultdata_score);
        $db->bind("qContent", $this->resultdata_result);
        return $db->execute();
    }

}