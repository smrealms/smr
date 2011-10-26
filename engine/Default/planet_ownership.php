<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menue();

$PHP_OUTPUT.=('<p>');

if (!$planet->hasOwner())
{
	$PHP_OUTPUT.=('The planet is unclaimed.');
	$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Take Ownership');
	$PHP_OUTPUT.=('</form>');
}
else
{
	if ($planet->getOwnerID() != $player->getAccountID())
	{
		$PHP_OUTPUT.=('You can claim the planet when you enter the correct password.');
		$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
		$PHP_OUTPUT.=('<input type="text" name="password" id="InputFields">&nbsp;&nbsp;&nbsp;');
		$PHP_OUTPUT.=create_submit('Take Ownership');
		$PHP_OUTPUT.=('</form>');
	}
	else
	{
		$PHP_OUTPUT.=('You can set a password for that planet.');
		$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
		$PHP_OUTPUT.=('<input type="text" name="password" value="'.htmlspecialchars($planet->getPassword()).'" id="InputFields">&nbsp;&nbsp;&nbsp;');
		$PHP_OUTPUT.=create_submit('Set Password');
		$PHP_OUTPUT.=('</form>');

		$PHP_OUTPUT.=('You can rename the planet.');
		$PHP_OUTPUT.=create_echo_form(create_container('planet_ownership_processing.php', ''));
		$PHP_OUTPUT.=('<input type="text" name="name" value="'.htmlspecialchars($planet->getName()).'" id="InputFields">&nbsp;&nbsp;&nbsp;');
		$PHP_OUTPUT.=create_submit('Rename');
		$PHP_OUTPUT.=('</form>');
	}
}

$PHP_OUTPUT.=('</p>');

?>