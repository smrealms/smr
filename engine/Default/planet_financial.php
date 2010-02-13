<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());

$template->assign('PageTopic','Planet : '.$planet->planet_name.' [Sector #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
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
while ($planet->maturity < $curr_time && $planet->maturity > 0) {

	// calc the interest for the time
	$interest = $planet->bonds * $rate - $planet->bonds;

	// transfer money to free avail cash
	$planet->credits += $planet->bonds + $interest;

	// reset bonds
	$planet->bonds = 0;

	// reset maturity
	$planet->maturity = 0;

	// update planet
	$planet->update();

}

$PHP_OUTPUT.=('<p>Balance: <b>' . number_format($planet->credits) . '</b></p>');
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

$bond_time = 48 / Globals::getGameSpeed($player->getGameID());

$PHP_OUTPUT.=('<p>You are able to transfer this money into a saving bond.<br />');
$PHP_OUTPUT.=('It remains there for ' . floor($bond_time) . ' hours');
//php doesn't like floats with modulus operator...so we have to make them both integers with same ratio
//to be safe we will make it * 10000 (it allows us to have speeds like 1.2501)
$speed = Globals::getGameSpeed($player->getGameID()) * 10000;
if (480000 % $speed != 0)
	$PHP_OUTPUT.=(', ' . round(($bond_time - floor($bond_time)) * 60) . ' minutes');

$PHP_OUTPUT.=(' and will gain ' . ($rate * 100 - 100) . '% interest.<br /><br />');

if ($planet->bonds > 0) {

	$PHP_OUTPUT.=('Right now there are ' . number_format($planet->bonds) . ' credits bonded');

	if ($planet->maturity > 0) {

		$maturity = $planet->maturity - $curr_time;
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