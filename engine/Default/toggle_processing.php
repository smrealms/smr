<?php
if($var['toggle']=='WeaponHiding') {
	$player->setDisplayWeapons(!$player->isDisplayWeapons());
}
else if($var['toggle']=='AJAX') {
	$account->setUseAJAX(!$account->isUseAJAX());
}
if(!USING_AJAX) {
	$container = create_container('skeleton.php');
	if(isset($var['referrer'])) $container['body'] = $var['referrer'];
	else $container['body'] = 'current_sector.php';
	forward($container);
}
