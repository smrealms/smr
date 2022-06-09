<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

$player->setCustomShipName($var['ShipName']);
$account->decreaseTotalSmrCredits($var['cost']);

$container = Page::create('current_sector.php');
$container['msg'] = 'Thanks for your purchase! Your ship is ready!<br /><small>If your ship is found to use HTML inappropriately you may be banned. If your ship does contain inappropriate HTML, please notify an admin ASAP.</small>';
$container->go();
