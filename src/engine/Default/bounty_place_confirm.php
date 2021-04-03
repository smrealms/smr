<?php declare(strict_types=1);

$template->assign('PageTopic', 'Place Bounty');

Menu::headquarters();

// get this guy from db
$bountyPlayer = SmrPlayer::getPlayerByPlayerID($var['player_id'], $player->getGameID());

$template->assign('Amount', number_format($var['amount']));
$template->assign('SmrCredits', number_format($var['SmrCredits']));
$template->assign('BountyPlayer', $bountyPlayer->getLinkedDisplayName());

$container = Page::create('bounty_place_confirm_processing.php');
$container['account_id'] = $bountyPlayer->getAccountID();
$container->addVar('amount');
$container->addVar('SmrCredits');
$container->addVar('LocationID');
$template->assign('ProcessingHREF', $container->href());
