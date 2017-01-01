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

    public function setUser($userId)
    {
        $this->resultdata_user = $userId;
    }

    public function save()
    {
        $db = new db();
        $db->query("INSERT INTO result(result_time, resultdata_user, resultdata_result)
                  VALUES(:qTime, :qUser, :qContent)");
        $db->bind("qTime", time());
        $db->bind("qUser", $this->resultdata_user);
        $db->bind("qContent", $this->resultdata_result);
        return $db->execute();
    }

}