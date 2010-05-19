<?php
//session_start();
include_once('lib/login.process.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Conforming XHTML 1.0 Strict Template</title>
	
	    <link rel="stylesheet" type="text/css" href="jquery.popup.css">
		
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript" src="jquery.blockUI.js"></script>
		<script type="text/javascript" src="jquery.pop.js"></script>
		
	</head>
	<body>
    
    <!-- HIDDEN LOGIN FORM -->
    <div id="login" class="hidden">
        <a href="#" class="close" >[ x ]</a>
        <form action="lib/login.process.php" method="POST">
            <label>Username:</label><input type="text" name="username" value="<?php echo $cookie_user ?>" />
            <label>Password:</label><input type="password" name="password" value="<?php echo $passKey ?>" />
            <input type="checkbox" name="remember" value=true /> Remember Me
            <input type="hidden" value="<?php echo $cookie_id ?>" name="cookie_id">
            <input type="submit" value="login" name="action" />
        </form>
    </div>

    <!-- END OF LOGIN FORM -->

    <a href="javascript:void(0)" class="login">Click Me!</a>

	
	
	</body>
</html>