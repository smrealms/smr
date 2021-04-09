<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$message = htmlentities(trim(Request::get('message')), ENT_COMPAT, 'utf-8');

if (empty($message)) {
	create_error('You have to enter text to send a message!');
}

// send to all council members
$councilMembers = Council::getRaceCouncil($player->getGameID(), $var['race_id']);
foreach ($councilMembers as $accountID) {
	$player->sendMessage($accountID, MSG_POLITICAL, $message, true, $player->getAccountID() != $accountID);
}

$container = Page::create('skeleton.php', 'current_sector.php');
$container['msg'] = '<span class="green">SUCCESS: </span>Your message has been sent.';
$container->go();
