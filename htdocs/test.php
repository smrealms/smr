<?php
$fp = fsockopen('unix:///tmp/ircbot.sock');

define('EOL',"\n");
//fputs($fp, 'PRIVMSG Page '.$_REQUEST['t'].EOL);
fputs($fp, $_REQUEST['t'].EOL);

?>
