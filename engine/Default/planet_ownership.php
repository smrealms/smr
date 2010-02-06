<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));
if (!$player->isLandedOnPlanet()) {
	
	create_error('You are not on a planet!');
	return;
	
}

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());

$template->assign('PageTopic','Planet : '.$planet->planet_name.' [Sector #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
create_planet_menue();

$PHP_OUTPUT.=('<p>');

if ($planet->owner_id == 0) {

	$PHP_OUTPUT.=('The planet is unclaimed.');
	$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Take Ownership');
	$PHP_OUTPUT.=('</form>');

} else {

	if ($planet->owner_id != $player->getAccountID()) {

		$PHP_OUTPUT.=('You can claim the planet when you enter the correct password.');
		$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
		$PHP_OUTPUT.=('<input type="text" name="password" id="InputFields">&nbsp;&nbsp;&nbsp;');
		$PHP_OUTPUT.=create_submit('Take Ownership');
		$PHP_OUTPUT.=('</form>');

	} else  {

		$PHP_OUTPUT.=('You can set a password for that planet.');
		$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
		$PHP_OUTPUT.=('<input type="text" name="password" value="'.$planet->password.'" id="InputFields">&nbsp;&nbsp;&nbsp;');
		$PHP_OUTPUT.=create_submit('Set Password');
		$PHP_OUTPUT.=('</form>');

		$PHP_OUTPUT.=('You can rename the planet.');
		$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
		$PHP_OUTPUT.=('<input type="text" name="name" value="'.$planet->planet_name.'" id="InputFields">&nbsp;&nbsp;&nbsp;');
		$PHP_OUTPUT.=create_submit('Rename');
		$PHP_OUTPUT.=('</form>');

	}
}

$PHP_OUTPUT.=('</p>');

?>