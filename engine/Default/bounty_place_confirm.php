<?php
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

// get request variables
if(isset($_REQUEST['amount']))
	SmrSession::updateVar('BountyAmount',empty($_REQUEST['amount'])?0:$_REQUEST['amount']);
if(isset($_REQUEST['smrcredits']))
	SmrSession::updateVar('BountySmrCredits',empty($_REQUEST['smrcredits'])?0:$_REQUEST['smrcredits']);
if(isset($_REQUEST['account_id']))
	SmrSession::updateVar('BountyAccountID',$_REQUEST['account_id']);

$amount = $var['BountyAmount'];
$smrCredits = $var['BountySmrCredits'];
$account_id = $var['BountyAccountID'];

if ($account_id == 0)
	create_error('Uhhh...who is [Please Select]?');
if (!is_numeric($account_id))
	create_error('Please select a player');

if (!is_numeric($amount)||!is_numeric($smrCredits))
	create_error('Numbers only please');

$amount = round($amount);
if ($player->getCredits() < $amount)
	create_error('You dont have that much money.');

$smrCredits = round($smrCredits);
if ($account->getSmrCredits() < $smrCredits)
	create_error('You dont have that many SMR credits.');

if ($amount <= 0 && $smrCredits <= 0)
	create_error('You must enter an amount greater than 0');

if ((empty($amount) && empty($smrCredits)) || empty($account_id))
	create_error('Don\'t you want to place bounty?');

$template->assign('PageTopic','Placing a bounty');

include(get_file_loc('menue.inc'));
if ($sector->has_hq()) $PHP_OUTPUT.=create_hq_menue();
else $PHP_OUTPUT.=create_ug_menue();

// get this guy from db
$bounty_guy =& SmrPlayer::getPlayer($account_id, $player->getGameID());

$PHP_OUTPUT.=('Are you sure you want to place a <span class="creds">' . number_format($amount) .
	  '</span> credits and <span class="yellow">' . number_format($smrCredits) .
	  '</span> SMR credits bounty on <span class="yellow">'.$bounty_guy->getPlayerName().'</span>?');

$container = array();
$container['url'] = 'bounty_place_processing.php';
$container['account_id'] = $bounty_guy->getAccountID();
$container['amount'] = $amount;
$container['SmrCredits'] = $smrCredits;
transfer('LocationID');

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');

?>