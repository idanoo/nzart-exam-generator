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

    public static function login($userName = false, $password = false)
    {
        if(isset($_SESSION['userName']) && isset($_SESSION['userId'])) return true;

        if(isset($userName) && isset($password)) {
            return self::_login($userName, $password, false);
        }

        return false;
    }

    private static function _login($userName = false, $password = false, $token = false) {
        if($token && !$password) {
            //query token
        } else if ($userName && $password) {
            $user = User::getWhere("userdata_username = '".$userName."'");
            if (is_object($user)) {
                if (password_verify($password, $user->_getHash())) {
                    setcookie("userName", $user->getUserName(), COOKIE_EXPIRY);
                    $_SESSION['username'] = $user->getUserName();
                    $_SESSION['userId'] = $user->getId();
                    return true;
                }
            }
        }
        return false;
    }

    public static function getUserFromSession()
    {
        return self::getWhere("user_id = '".$_SESSION['userId']."'");
    }

    public static function register()
    {

    }

    public function _getHash()
    {
        return $this->userdata_password;
    }

    public function getUserName()
    {
        return $this->userdata_username;
    }
}