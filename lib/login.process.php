<?php
// check for cookies

$cookie_id = '';
$cookie_user = '';
$passKey = '';

// check if a cookie exists
if (isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
    $cookie_id = $_COOKIE['user_id'];       // md5 user id stored within cookie
    $cookie_user = $_COOKIE['username'];    // user name stored within cookie
    
    $passKey = "*****";                     // fake key sent to browser if cookies are set
}

if (isset($_REQUEST['action'])) 
{
    switch ($_REQUEST['action'])
    {
        case 'login':
            include_once('Login.class.php');
            $login = new Login();
            
            $username = htmlspecialchars($_POST['username']);
            
            $passwordArr['form'] = htmlspecialchars(sha1($_POST['password']));
            $passwordArr['passKey'] = sha1($passKey);
            //$passwordArr['db'] = $login->checkCookie($_POST['cookie_id'], $username);
            
            if ($passwordArr['form'] != $passwordArr['passKey']) {
                $password = $passwordArr['form'];
            }
            else {
                $password = $passwordArr['db'];
            }

            $remember = (isset($_POST['remember']) ? true : false);
            
            // ensure username and password actually contain values.
            if ($username=="" || $password=="") {
                header("Location: ../index.php?empty");
                exit();
            } 
            // log the user in
            else {
                if($login->userLogin($username, $password, $remember)) {
                    // correct login, goto the admin area
                    header('Location: ../welcome.php');
                    exit();
                }
                else {
                    // error with the login exit script
                    header("Location: ../index.php?error");
                    exit();
                }
            }
        break;
       
        /* ================ */
        
        case 'logout':
	        include_once('Login.class.php');
	        $login = new Login();

			// redirect to index and exit script
	        if($login->userLogout()) {
				header('Location: ../index.php');
				exit();
			}
        break;
    }
}
?>