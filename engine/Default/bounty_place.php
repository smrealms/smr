<?php
$sector =& $player->getSector();

$template->assign('PageTopic','Place a Bounty');

require_once(get_file_loc('menu_hq.inc'));
if ($sector->hasHQ()) {
	create_hq_menu();
}
else {
	create_ug_menu();
}

$container = create_container('skeleton.php','bounty_place_confirm.php');
transfer('LocationID');
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('Select the player you want to add the bounty to<br />');
$PHP_OUTPUT.=('<select name="player_id" size="1" id="InputFields">');
$PHP_OUTPUT.=('<option value="0">[Please Select]</option>');

$db->query('SELECT player_id, player_name FROM player JOIN account USING(account_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id != ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY player_name');
while($db->nextRecord()) {
	$PHP_OUTPUT.=('<option value="' . $db->getInt('player_id') . '">' . $db->getField('player_name') . '</option>');
}
$PHP_OUTPUT.=('</select>');

$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=('Enter the amount you wish to place on this player<br />');
$PHP_OUTPUT.=('<table class="standardnobord"><tr><td>Credits:</td><td><input type="number" name="amount" maxlength="10" size="10" id="InputFields"></td></tr>');
$PHP_OUTPUT.=('<tr><td>Smr Credits:</td><td><input type="number" name="smrcredits" maxlength="10" size="10" id="InputFields"></td></tr></table>');

$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=create_submit('Place');
