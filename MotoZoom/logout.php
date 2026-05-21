<?php
session_start();

//Thoroughly destroy all session details
setcookie(session_name(), '', 100);
session_unset();
session_destroy();
$_SESSION = array();
  header("Location: index.php"); //Return to home page
  exit(); //Stop any further code from being executed
?>