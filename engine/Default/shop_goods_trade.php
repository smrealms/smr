<?php
$template->assign('PageTopic','Negotiate Price');
require_once('shop_goods.inc');
// creates needed objects
$port =& $player->getSectorPort();
// get values from request
$good_id = $var['good_id'];
$portGood = $port->getGood($good_id);

if ($var['bargain_price'] > 0) {
	$bargain_price = $var['bargain_price'];

	$PHP_OUTPUT.=('<p>I can\'t accept your offer. It\'s still too ');
	if ($portGood['TransactionType'] == 'Sell')
		$PHP_OUTPUT.=('high');
	elseif ($portGood['TransactionType'] == 'Buy')
		$PHP_OUTPUT.=('low');
	$PHP_OUTPUT.=('.</p>');
}
else
	$bargain_price = $var['offered_price'];

$PHP_OUTPUT.=('<p>I would ');
$portGood = $port->getGood($good_id);
if ($portGood['TransactionType'] == 'Sell')
	$PHP_OUTPUT.=('buy ');
elseif ($portGood['TransactionType'] == 'Buy')
	$PHP_OUTPUT.=('offer you ');
$PHP_OUTPUT.=($var['amount'] . ' pcs. of ' . $portGood['Name'] . ' for ' . $var['offered_price'] . ' credits!<br />');
$PHP_OUTPUT.=('Note: In order to maximize your experience you have to bargain with the port owner, unless you have maximum relations (1000) with that race, which gives full experience without the need to bargain.</p>');

$container = array();
$container['url'] = 'shop_goods_processing.php';

transfer('amount');
transfer('good_id');
transfer('offered_price');
transfer('ideal_price');
transfer('number_of_bargains');
transfer('overall_number_of_bargains');

$PHP_OUTPUT.=create_echo_form($container);
//gives value 0-1
if (isset($var['ideal_price'])) {
	// transfer this value
	transfer('ideal_price');

	// return this value
	$ideal_price = $var['ideal_price'];
}
if (isset($var['offered_price'])) {
	// transfer this value
	transfer('offered_price');

	// return this value
	$offered_price = $var['offered_price'];
}

$PHP_OUTPUT.=('<input type="text" name="bargain_price" value="'.$bargain_price.'" id="InputFields" class="center" style="width:75;vertical-align:middle;">&nbsp;');
$PHP_OUTPUT.=('<!-- all needed information to calculate the ideal price -->');
$PHP_OUTPUT.=('<!-- Trade.Amount:Good.BasePrice:Good.Distance:Port.Good.Amount:Port.Good.Max:Relations:Port.Level -->');
$PHP_OUTPUT.=('<!--('.$var['amount'].':'.$portGood['BasePrice'].':'.$port->getGoodDistance($good_id).':'.$portGood['Amount'].':'.$portGood['Max'].':'.$player->getRelation($port->getRaceID()).':'.$port->getLevel().')-->');
$PHP_OUTPUT.=create_submit('Bargain (1)');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.= '<script type="text/javascript">
					window.document.FORM.bargain_price.select();
					window.document.FORM.bargain_price.focus();
				</script>';

$PHP_OUTPUT.=('<p>Distance Index: '. $port->getGoodDistance($good_id) .'</p>');

$PHP_OUTPUT.=('<h2>Or do you want:</h2>');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'shop_goods.php'));
$PHP_OUTPUT.=create_submit('Select a different good');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'current_sector.php'));
$PHP_OUTPUT.=create_submit('Leave Port');
$PHP_OUTPUT.=('</form>');

?>