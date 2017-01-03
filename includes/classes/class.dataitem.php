<?php

class DataItem {

    public static function getById($id) {
        $db = new db();
        $db->query("SELECT * FROM `".static::_getType()."` WHERE ".static::_getType()."_id = :id");
        $db->bind(":id", $id);
        $result = $db->single();
        if(!$result) return false;
        $className = static::_getClass();
        $obj = new $className();
        foreach ($result as $key=>$val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    public static function getAllWhere($where = false, $orderBy = false, $join = false, $limit = false) {
        $Objarr = [];
        $db = new db();
        $db->query("SELECT * FROM `".static::_getType()."` ".($join?$join:"").($where?" WHERE ".$where."":"")." ".($orderBy?" ".$orderBy." ":" ").($limit?"LIMIT ".$limit:""));
        $results = $db->resultset();
        if(!$results) return false;
        foreach ($results as $result) {
            $obj = new static();
            foreach ($result as $key=>$val) {
                $obj->$key = $val;
            }
            $Objarr[] = $obj;
        }
        return $Objarr;
    }

    public static function getWhere($where = false, $orderBy = false, $join = false, $limit = false) {
        $db = new db();
        $db->query("SELECT * FROM `".static::_getType()."` ".($join?$join:"").($where?" WHERE ".$where."":"")." ".($orderBy?" ".$orderBy."":"").($limit?"LIMIT ".$limit:""));
        $result = $db->single();
        if(!$result) return false;
        $obj = new static();
        foreach ($result as $key=>$val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    public static function count($query)
    {
        $db = new db();
        $db->query($query);
        return array_pop($db->single());
    }

    public function getId() {
        $typeName = static::_getType();
        $id = $typeName."_id";
        return $this->$id;
    }

    public static function create($PDOobj)
    {
        $className = static::_getClass();
        $obj = new $className();
        foreach ($PDOobj as $key=>$val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    protected function _getTime() {
        return static::_getType()."_time";
    }

    public function getTime() {
        $timeColumn = self::_getTime();
        return $this->$timeColumn;
    }
}