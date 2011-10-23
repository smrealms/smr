<?php
if(isset($var['msg']))
	$template->assign('Message', $var['msg']);
$template->assign('ValidateFormHref', SmrSession::get_new_href(create_container('validate_processing.php')));

?>