<?php

/*
@author:    cNicoll
@name:	    
@date:      01-26-10_14-24

RELEASE NOTES:
==========================================================================================
@version 1.0 | 01-26-10_14-24
	
HEADER:
==========================================================================================
public:
	Login()
	getUserId()
	setUserId($id)
private:
	$model
	$errors
	$userId
	$username
	$password
	$cookieExpireTime
	$domain
	$logged
*/

include_once('model.class.php');

define ('SUGAR', 'a5a33aD51');

define('REMEMBER_EXPIRE', 60*60*24*30);		// expire in 30 days (2592000)
define('EXPIRE_DEFAULT', 3600);				// expire in 1 hour
define('EXPIRE_NOW',  -3600);              // expire -1 hour ago

class Login
{
    private $model;
    private $errors = array();
    
    private $userId;
    private $username;
    private $password;
    
    private $cookieExpireTime;
    private $domain;
    
    private $logged;
    
    /*
    * @param
    *	
    * @return
    *	
    * @comment
    * 	Set the default expire times on cookies
	*	Set the Domain (currently not in use)
	*	Set the logged status to FALSE (currently not used)
    * @time
    * 	01-26-10_14-24
    */
    public function Login()
    {
        $this->model = new Model();
        
        $this->setCookieExpireTime(EXPIRE_DEFAULT);
        $this->setDomain('localhost/');
        $this->setLogged(false);
    }
    
    // @return user id
    public function getUserId()
    {
        return $this->userId;
    }
    
    // @param user id
    public function setUserId($id)
    {
        $this->userId = $id;
    }
    
    // @return username
    public function getUserName()
    {
        return $this->username;
    }
    
    // @param username
    public function setUserName($u)
    {
        $this->username = $u;
    }
    
    // @return password
    public function getPassword()
    {
        return $this->password;
    }
    
   // @param password
    public function setPassword($p)
    {
        $this->password = $p;
    }
    
    // @return value of time before cookie expires
    public function getCookieExpireTime()
    {
        return $this->cookieExpireTime;
    }
    
    // @param amount of time for cookie to expire
    public function setCookieExpireTime($time)
    {
        $this->cookieExpireTime = time()+$time;
    }
    
    // @return domain nane
    public function getDomain()
    {
        return $this->domain;
    }
    
    // @param domain nane
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
    
    // @return bool
    public function getLogged()
    {
        return $this->logged;
    }
    
    // @param bool if ok
    public function setLogged($bool)
    {
        $this->logged = $bool;
    }
    
    // @return error array
    public function getErrors()
    {
        return $this->errors;
    }
        
    /* =====================================================================================================================
    ========================================================================================================================
    ======================================================================================================================== */
 
    /*
    * @param
    *	string 	username
	*	string 	password
	*	bool	remember user
    * @return
    *	bool if user can login
    * @comment
    * 	Creates a session for the user, also sets a cookie to the default value. However
	*	if the user has opt to save their password a cookie is set for the REMEMBER_EXPIRE
    * @time
    * 	01-27-10_13-02
    */
    public function userLogin($username, $password, $remember)
    {
        $user = $this->model->checkLogin($username, $password);
        // at least an ID and username. Other fields are optional. PASSWORD IS NOT TO BE SENT
        if (sizeof($user) >= 2) {
            //session_start();
            
            // set login details
            $this->setUserId($user['id']);
            $this->setUserName($user['username']);
            $this->setPassword($password);
            
            // set session
            $_SESSION['user_id'] = md5(SUGAR+$this->getUserID());
            $_SESSION['username'] = $this->getUserName();
            
            // make cookie
            $remember ? $this->setCookieExpireTime(REMEMBER_EXPIRE) : $this->setCookieExpireTime(EXPIRE_DEFAULT);
            setcookie("user_id", md5(SUGAR+$this->getUserID()), $this->getCookieExpireTime(), "/");
            setcookie("username", $this->getUserName(), $this->getCookieExpireTime(), "/");
            
            // return status of logged
            $this->setLogged(true);
            return $this->getLogged();
        }
        // error
        return false;
    }
    
    /*
    * @param
    *	
    * @return
    *	
    * @comment
    * 	
    * @time
    * 	01-27-10_15-51
    */
    public function checkCookie($userId, $username)
    {
        // get the userid and password, compare the id against the hash key, if its true return the password
        $cookie = $this->model->checkCookie($username);
        if (md5(SUGAR+$cookie['user_id']) == $userId) {
            return $cookie['password'];
        }
    }
    
    /*
    * @param
    *	
    * @return
    *	
    * @comment
    * 	
    * @time
    * 	01-27-10_13-57
    */
    public function userLogout()
    {
        // reset user data
        $this->setUserId("");
        $this->setUserName("");
        $this->setPassword("");
        $this->setCookieExpireTime(EXPIRE_NOW);
        $this->setLogged(false);
        
        // destroy sessions
        session_start();
		session_unset();
		session_destroy();
		
		// destroy cookies
		setcookie("user_id", $this->getUserID(), $this->getCookieExpireTime(),"/");
        setcookie("username", $this->getUserName(), $this->getCookieExpireTime(),"/");
		
		return true;
    }
}

?>