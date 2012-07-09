<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu();

$PHP_OUTPUT.=('<p>Balance: <b>' . number_format($planet->getCredits()) . '</b></p>');

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
$PHP_OUTPUT.=('It remains there for ' . format_time($bond_time) . ' and will gain ' . ($planet->getInterestRate() * 100) . '% interest.<br /><br />');

if ($planet->getBonds() > 0) {

	$PHP_OUTPUT.=('Right now there are ' . number_format($planet->getBonds()) . ' credits bonded');

	if ($planet->getMaturity() > 0) {

		$maturity = $planet->getMaturity() - TIME;
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