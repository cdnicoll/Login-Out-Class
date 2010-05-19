<?php

include_once('sql.class.php');

class Model
{
    private $db;    // Hold a database object
    
    public function Model()
    {
        $this->db = new Database('localhost','root','root','faithwilson');
    }
    

    public function checkLogin($u,$p)
    {
        $user = array();
    	$this->db->connect();
	    if($this->db->select('users','*', 'username="'.$u.'"', NULL, NULL)) {
	        $user = $this->db->getResult();
	    }
	    $this->db->disconnect();    // disconnect from DB
        
	    if (sizeof($user) >= 1) {
	        // !! THIS COULD BE A SMALL SECURITY HOLE, IN THE SENSE THAT WHEN LOGGING IN WITH COOKIES ALREADY IN PLACE
	        // !! THE PASSWORD CHECKS BOTH A NORMAL PASS KEY AND A SHA1 PASS KEY
	        // !!!
	        // may have fixed this within the process. I check there if a the cookie password has been set.
            if ($p == $user['password']) {
                unset($user['password']);
                echo '<pre>';
                	print_r($user);
                echo '</pre>';
                return $user;
            }
	    }
	    return -1;
    }
    
    public function checkCookie($u)
    {
        $user = array();
    	$this->db->connect();
    	if($this->db->select('users','id, password', 'username="'.$u.'"', NULL, NULL)) {
    	    $user = $this->db->getResult();
	    }
	    $this->db->disconnect();
	    
	    return $user;
    }
}
?>