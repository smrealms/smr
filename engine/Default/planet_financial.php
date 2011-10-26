<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menue();

$curr_time = TIME;

$lvl = $planet->getLevel();
if ($lvl < 9)
	$base = 1.02;
elseif ($lvl < 19)
	$base = 1.03;
elseif ($lvl < 29)
	$base = 1.06;
elseif ($lvl < 39)
	$base = 1.025;
elseif ($lvl < 49)
	$base = 1.02;
elseif ($lvl < 59)
	$base = 1.015;
elseif ($lvl < 69)
	$base = 1.01;
else
	$base = 1.009;

$rate = pow($base,2);

// grant for all days we didn't got
while ($planet->getMaturity() < $curr_time && $planet->getMaturity() > 0)
{
	// calc the interest for the time
	$interest = $planet->getBonds() * $rate - $planet->getBonds();

	// transfer money to free avail cash
	$planet->increaseCredits($planet->getBonds() + $interest);

	// reset bonds
	$planet->setBonds(0);

	// reset maturity
	$planet->setMaturity(0);

	// update planet
	$planet->update();

}

$PHP_OUTPUT.=('<p>Balance: <b>' . number_format($planet->getCredits()) . '</b></p>');
if (!empty($interest))
	$PHP_OUTPUT.=('<span style="font-size:75%">Since your last visit<br />you got <span class="creds bold">' . number_format(round($interest)) . '</span> credits interest!</span>');


$PHP_OUTPUT.=create_echo_form(create_container('planet_financial_processing.php', ''));
$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td colspan="2" align="center"><input type="text" name="amount" value="0" id="InputFields" style="text-align:right;width:152;"></td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Deposit');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Withdraw');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$bond_time = BOND_TIME / Globals::getGameSpeed($player->getGameID());

$PHP_OUTPUT.=('<p>You are able to transfer this money into a saving bond.<br />');
$PHP_OUTPUT.=('It remains there for ' . format_time($bond_time) . ' and will gain ' . ($rate * 100 - 100) . '% interest.<br /><br />');

if ($planet->getBonds() > 0) {

	$PHP_OUTPUT.=('Right now there are ' . number_format($planet->getBonds()) . ' credits bonded');

	if ($planet->getMaturity() > 0) {

		$maturity = $planet->getMaturity() - $curr_time;
		$hours = floor($maturity / 3600);
		$minutes = ceil(($maturity - ($hours * 3600)) / 60);

		$PHP_OUTPUT.=(' and will come to maturity in ');
		if ($hours > 0) {

			if ($hours > 1)
				$PHP_OUTPUT.=($hours.' hours');
			else
				$PHP_OUTPUT.=('1 hour');

			if ($minutes > 0)
				$PHP_OUTPUT.=(' and ');

		}

		if ($minutes > 0) {

			if ($minutes > 1)
				$PHP_OUTPUT.=($minutes.' minutes');
			else
				$PHP_OUTPUT.=('1 minute');

		}

		$PHP_OUTPUT.=('.');

	}

}

$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=create_submit('Bond It!');

$PHP_OUTPUT.=('</form>');

?>