<?php
session_start(); 
if(!isset($_SESSION['user_id'])) {
   header('location: index.php');
   exit();
}



$s = 'a5a33aD51';
?>

<h1>welcome <?php echo $_COOKIE['username'] ?>, to the elite</h1>
<a href='lib/login.process.php?action=logout'>Logout</a>