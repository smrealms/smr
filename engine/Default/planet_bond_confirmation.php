<?php
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

$bondDuration = format_time($planet->getBondTime());
$returnHREF = $planet->getFinancesHREF();

$PHP_OUTPUT = <<<HTML
<h2>Planetary Bond Confirmation</h2>
<p>All credits on the planet at the time of confirmation, along with any
credits currently bonded (and any partial interest they may have accrued),
will be added to a new bond. You will not be able to access these funds until
the bond matures in $bondDuration.</p>

<p>Please confirm to proceed.</p>

<form id="BondConfirmForm" method="POST" action="$returnHREF">
	<table>
		<tr>
			<td><input type="submit" name="action" value="Confirm" id="confirmBond" /></td>
			<td><input type="submit" name="action" value="Cancel" id="cancelBond" /></td>
		</tr>
	</table>
</form>
HTML

?>
