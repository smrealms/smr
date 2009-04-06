<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

$container = array();
$container['url'] = 'skeleton.php';
if ($sector->has_hq()) {

	$container['body'] = 'government.php';
	$type = 'HQ';

} else {

	$container['body'] = 'underground.php';
	$type = 'UG';

}
$action = $_REQUEST['action'];
// if we don't have a yes we leave immediatly
if ($action != 'Yes')
	forward($container);

// get values from container
$amount = $var['amount'];
$smrCredits = $var['SmrCredits'];
$account_id = $var['account_id'];
if (!$amount&&!$smrCredits)
	create_error('You must enter an amount');
if ($amount < 0)
	create_error('You must enter a positive amount');
if ($smrCredits < 0)
	create_error('You must enter a positive credits amount');
// take the bounty from the cash
$player->decreaseCredits($amount);
$account->decreaseSmrCredits($smrCredits);

$player->increaseHOF($smrCredits,array('Bounties','Placed','SMR Credits'));
$player->increaseHOF($amount,array('Bounties','Placed','Money'));
$player->increaseHOF(1,array('Bounties','Placed','Number'));

$placed =& SmrPlayer::getPlayer($account_id, $player->getGameID());
$placed->increaseCurrentBountyAmount($type,$amount);
$placed->increaseCurrentBountySmrCredits($type,$smrCredits);
$placed->increaseHOF($smrCredits,array('Bounties','Received','SMR Credits'));
$placed->increaseHOF($amount,array('Bounties','Received','Money'));
$placed->increaseHOF(1,array('Bounties','Received','Number'));

//Update for top bounties list
$player->update();
$account->update();
$placed->update();
forward($container);

?>
