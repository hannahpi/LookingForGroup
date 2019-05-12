<?php
function session_start_once(){
  if (!isset($_SESSION)) {
      session_start();
  }
}

function errormail($email, $message, $errorInfo, $diemsg) {
	//send email with confirmation link
	$headers = "From: ". $GLOBALS['BUG_MAIL_NAME']. " <" . $GLOBALS['BUG_EMAIL'] .">";
	$subject = "Error for $email";
	$message .= "Additional information: $errorInfo \n "
	           ." no session variables here. \n   ";
	mail($GLOBALS['ACTUAL_ADMIN'],$subject,$message,$headers);
	echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['CSS'] . '" />';
	die("$diemsg");
}

 ?>
