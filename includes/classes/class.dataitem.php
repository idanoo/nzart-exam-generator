<?php

class DataItem {

    protected $_db;

    function __construct() {
        $this->_db = new db(); //Will optimise this to get existing conn at some point.
    }

    public function getById($id) {
        $this->_db->query("SELECT * FROM `".static::_getType()."` WHERE ".static::_getType()."_id = :id");
        $this->_db->bind(":id", $id);
        return $this->_db->getObject();
    }

    private function _getAllWhere($where = false, $orderBy = false, $join = false, $limit = false) {
        $Objarr = array();
        $typeName = static::_getType();
        $className = static::_getClass();
        $this->_db->query("SELECT * FROM `".$typeName."` ".($join?$join:"").($where?" WHERE ".$where."":"")." ".($orderBy?" ".$orderBy." ":" ").($limit?"LIMIT ".$limit:""));
        $results = $this->_db->resultset();
        if(!$results) return false;
        foreach ($results as $result) {
            $obj = new $className();
            foreach ($result as $key=>$val) {
                $obj->$key = $val;
            }
            $Objarr[] = $obj;
        }
        return $Objarr;
    }

    public static function getAllWhere($where = false, $orderBy = false, $join = false, $limit = false) {
        $me = new static();
        return $me->_getAllWhere($where, $orderBy, $join, $limit);
    }

    private function _getWhere($where = false, $orderBy = false, $join = false, $limit = false) {
        $typeName = static::_getType();
        $className = static::_getClass();
        $this->_db = new db();
        $this->_db->query("SELECT * FROM `".$typeName."` ".($join?$join:"").($where?" WHERE ".$where."":"")." ".($orderBy?" ".$orderBy."":"").($limit?"LIMIT ".$limit:""));
        $result = $this->_db->single();
        if(!$result) return false;
        $obj = new $className();
        foreach ($result as $key=>$val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    public static function getWhere($where = false, $orderBy = false, $join = false, $limit = false) {
        $me = new static();
        return $me->_getWhere($where, $orderBy, $join, $limit);
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
        $timeColumn = static::_getCreationTime();
        return $this->$timeColumn;
    }
}