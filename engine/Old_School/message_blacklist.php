<?php

	$smarty->assign('PageTopic','PlAYER BLACKLIST');

	include($ENGINE . 'global/menue.inc');
	$PHP_OUTPUT.=create_message_menue();
 
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
 		$PHP_OUTPUT.= '<br><br>';
 	}
  $PHP_OUTPUT.= '<h2>Blacklisted Players</h2><br>';
  
  $db = new SMR_DB();
  
  $db->query('SELECT player.player_name as player_name, message_blacklist.entry_id as entry_id FROM player, message_blacklist WHERE player.account_id = message_blacklist.blacklisted_id AND message_blacklist.account_id=' . SmrSession::$account_id . ' AND message_blacklist.game_id = player.game_id AND player.game_id = ' . SmrSession::$game_id);
  
  if($db->nf()) {
  	
		$container = array();
		$container['url'] = 'message_blacklist_del.php';
		$form = create_form($container,'Remove Selected');
		$PHP_OUTPUT.= $form['form'];
	
		$PHP_OUTPUT.= '<table class="standard" cellspacing="0" cellpadding="0"><tr><th>Option</th><th>Name</th>';
  	
		while($db->next_record()) {
			$row = $db->fetch_row();		
			$PHP_OUTPUT.= '<tr>';
			$PHP_OUTPUT.= '<td class="center shrink"><input type="checkbox" name="entry_ids[]" value="' . $row['entry_id'] . '"></td>';
			$PHP_OUTPUT.= '<td>' . $row['player_name'] . '</td>';
			$PHP_OUTPUT.= '</tr>';
		}
		
  	$PHP_OUTPUT.= '</table><br>';
		$PHP_OUTPUT.= $form['submit'];
		$PHP_OUTPUT.= '</form><br>';
	
  }
  else {

  	$PHP_OUTPUT.= 'You are currently accepting all communications.<br>';
  	
  }
  
	$PHP_OUTPUT.= '<br><h2>Blacklist Player</h2><br>';
	$container = array();
	$container['url'] = 'message_blacklist_add.php';
	$form = create_form($container,'Blacklist');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Name:&nbsp;</td>
			<td class="mb"><input type="text" name="PlayerName" size="30"></td>
	</table><br>
	';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
	
?>
