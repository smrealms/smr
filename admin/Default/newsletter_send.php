<?php
$template->assign('PageTopic','Newsletter');

$template->assign('CurrentEmail', $account->getEmail());

$container = create_container('newsletter_send_processing.php', '');
$template->assignByRef('ProcessingContainer', $container);

?>
