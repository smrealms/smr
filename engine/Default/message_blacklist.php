<?php

$template->assign('PageTopic','Player Blacklist');

require_once(get_file_loc('menu.inc'));
create_message_menu();
 
if(isset($var['error'])) {
	switch($var['error']) {
		case(1):
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>Player does not exist.';
			break;
		case(2):
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>Player is already blacklisted.';
			break;
		case(3):
			$PHP_OUTPUT.= '<span class="yellow">' . $_REQUEST['PlayerName'] . '</span> has been added to your blacklist.';
			break;
		case(4):
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>No entries selected for deletion.';
			break;
		default:
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>Unknown error event.';
			break;
	}
	$PHP_OUTPUT.= '<br /><br />';
}
$PHP_OUTPUT.= '<h2>Blacklisted Players</h2><br />';

$db = new SmrMySqlDatabase();
$db->query('SELECT p.player_name, p.game_id, b.entry_id FROM player p JOIN message_blacklist b ON p.account_id = b.blacklisted_id AND b.game_id = p.game_id WHERE b.account_id=' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY p.game_id, p.player_name');

if($db->getNumRows()) {
	$container = array();
	$container['url'] = 'message_blacklist_del.php';
	$form = create_form($container,'Remove Selected');
	$PHP_OUTPUT.= $form['form'];
	
	$PHP_OUTPUT.= '<table class="standard"><tr><th>Option</th><th>Name</th><th>Game ID</th>';
	
	while($db->nextRecord()) {
		$row = $db->getRow();		
		$PHP_OUTPUT.= '<tr>';
		$PHP_OUTPUT.= '<td class="center shrink"><input type="checkbox" name="entry_ids[]" value="' . $row['entry_id'] . '"></td>';
		$PHP_OUTPUT.= '<td>' . $row['player_name'] . '</td>';
		$PHP_OUTPUT.= '<td>' . $row['game_id'] . '</td>';
		$PHP_OUTPUT.= '</tr>';
	}
	
	$PHP_OUTPUT.= '</table><br />';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form><br />';
}
else {
	$PHP_OUTPUT.= 'You are currently accepting all communications.<br />';
}

$PHP_OUTPUT.= '<br /><h2>Blacklist Player</h2><br />';
$container = array();
$container['url'] = 'message_blacklist_add.php';
$form = create_form($container,'Blacklist');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Name:&nbsp;</td>
		<td class="mb"><input type="text" name="PlayerName" size="30"></td>
	</tr>
</table><br />
';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</form>';
