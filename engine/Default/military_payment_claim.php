<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$smarty->assign('PageTopic','Military Payment Center');

include(ENGINE . 'global/menue.inc');
if ($sector->has_hq())
	$PHP_OUTPUT.=create_hq_menue();
else
	$PHP_OUTPUT.=create_ug_menue();

if ($player->military_payment > 0) {

	$PHP_OUTPUT.=('For your military help you have been paid <font color=yellow>'.$player->getMilitaryPayment().'</font> credits');

	$player->increaseHOF($player->military_payment,'military_claimed');

	// add to our cash
	$player->increaseCredits($player->military_payment);
	$player->military_payment = 0;
	$player->update();

} else
	$PHP_OUTPUT.=('You have done nothing worthy of military payment');

?>