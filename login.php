<?php session_start();

if(!defined('LOGINPASSWORD')){
	die('constant LOGINPASSWORD not defined');
}
elseif(isset($_POST['psw']) && $_POST['psw']==LOGINPASSWORD){
	$_SESSION['logged_in'] = true;
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	header('Location: '.$url);
	exit;
}
elseif(!isset($_SESSION['logged_in']) || $_SESSION['logged_in']!==true)
{
	?>
	<!DOCTYPE html>
	<html>
	  <head>
	    <title>Login</title>
	  </head>
	  <body>
		<form method="post">
		  <div class="container">
		    <label for="psw"><b>Password</b></label>
		    <input type="password" placeholder="Enter Password" name="psw" required>
		    <button type="submit">Login</button>
		  </div>
		</form>
	  </body>
	</html>
	<?php
	exit;
}

?>
