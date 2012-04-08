<?php
if(isset($var['msg']))
	$template->assign('Message', $var['msg']);
$template->assign('ValidateFormHref', SmrSession::getNewHREF(create_container('validate_processing.php')));

?>