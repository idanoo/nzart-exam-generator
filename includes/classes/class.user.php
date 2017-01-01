<?php

class User extends DataItem {

    public $user_id;
    public $userdata_username;
    public $userdata_password;

    public static function _getClass() {
        return "User";
    }

    public static function _getType() {
        return "user";
    }

    public static function loginOrRegister($data)
    {
        if(isset($data['register'])) {
            self::register($data['username'], $data['password']);
        } elseif(isset($data['login'])) {
            self::login($data['username'], $data['password']);
        }
    }

    public static function register($userName, $password)
    {
        if(isset($userName) && isset($password)) {
            return self::_register($userName, $password);
        }
        return false;    }

    public static function login($userName = false, $password = false)
    {
        if(isset($userName) && isset($password)) {
            return self::_login($userName, $password);
        }
        return false;
    }

    private static function _register($userName = false, $password = false)
    {
        if ($userName && $password) {
            $user = User::getWhere("userdata_username = '".$userName."'");
            if (!is_object($user)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db = new db();
                $db->query("INSERT INTO user(user_time, userdata_username, userdata_password)
                  VALUES(:qTime, :qUser, :qPassword)");
                $db->bind("qTime", time());
                $db->bind("qUser", $userName);
                $db->bind("qPassword", $hash);
                if($db->execute()) {
                    $_SESSION['username'] = $userName;
                    $_SESSION['userId'] = $db->lastInsertId();
                    return true;
                }
            }
        }
        return false;
    }

    private static function _login($userName = false, $password = false)
    {
        if ($userName && $password) {
            $user = User::getWhere("userdata_username = '".$userName."'");
            if (is_object($user)) {
                if (password_verify($password, $user->_getHash())) {
                    $_SESSION['username'] = $user->getUserName();
                    $_SESSION['userId'] = $user->getId();
                    return true;
                }
            }
        }
        return false;
    }

    public static function logout()
    {
        session_destroy();
        header("Location: //".$_SERVER['HTTP_HOST']);
        exit();
    }

    public static function getUserFromSession()
    {
        return self::getWhere("user_id = '".$_SESSION['userId']."'");
    }

    protected function _getHash()
    {
        return $this->userdata_password;
    }

    public function getUserName()
    {
        return $this->userdata_username;
    }

    public function storeuser($dataArray)
    {
        $user = new user();
        $user->setuser($dataArray);
        $user->setUser($this->getId());
        $user->save();
    }
}