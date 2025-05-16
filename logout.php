<?php 
ob_start();
session_start();

include('dbconfig.php');

unset($_SESSION['user']);

session_destroy();

header("location: login.php");

if(isset($_GET["session_expire"])) {
	$url= "login.php?session_expire=" . $_GET["session_expire"];
	unset($_SESSION['user']);
	 header("Location: login.php");
	 
}
exit;
?>
