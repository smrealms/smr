<?php
try
{
	require_once('config.inc');
	header('Location: '.URL.'/login.php');
	exit;
}
catch(Exception $e)
{
	handleException($e);
}
?>
