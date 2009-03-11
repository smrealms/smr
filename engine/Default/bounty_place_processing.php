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
$account_id = $var['account_id'];
if (!$amount)
	create_error('You must enter an amount');
if ($amount < 0)
	create_error('You must enter a positive amount');
// take the bounty from the cash
$player->decreaseCredits($amount);

$player->increaseHOF($amount,array('Bounties','Placed','Money'));
$player->increaseHOF(1,array('Bounties','Placed','Number'));

$placed =& SmrPlayer::getPlayer($account_id, $player->getGameID());
$placed->increaseCurrentBountyAmount($type,$amount);
$placed->increaseHOF($amount,array('Bounties','Received','Money'));
$placed->increaseHOF(1,array('Bounties','Received','Number'));

forward($container);

?>
