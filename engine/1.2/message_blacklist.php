<?php

	print_topic("PlAYER BLACKLIST");

	include(get_file_loc('menue.inc'));
	print_message_menue();
 
 	if(isset($var['error'])) {
 		switch($var['error']) {
 			case(1):
 				echo '<span class="red bold">ERROR: </span>Player does not exist.';
 				break;
 			case(2):
 				echo '<span class="red bold">ERROR: </span>Player is already blacklisted.';
 				break;
 			case(3):
 				echo '<span class="yellow">' . $_REQUEST['PlayerName'] . '</span> has been added to your blacklist.';
 				break;
 			case(4):
 				echo '<span class="red bold">ERROR: </span>No entries selected for deletion.';
 				break;
 			default:
 				echo '<span class="red bold">ERROR: </span>Unknown error event.';
 				break;
 		}
 		echo '<br><br>';
 	}
  echo '<h2>Blacklisted Players</h2><br>';
  
  $db = new SmrMySqlDatabase();
  
  $db->query('SELECT player.player_name as player_name, message_blacklist.entry_id as entry_id FROM player, message_blacklist WHERE player.account_id = message_blacklist.blacklisted_id AND message_blacklist.account_id=' . SmrSession::$old_account_id . ' AND message_blacklist.game_id = player.game_id AND player.game_id = ' . SmrSession::$game_id);
  
  if($db->nf()) {
  	
		$container = array();
		$container['url'] = 'message_blacklist_del.php';
		$form = create_form($container,'Remove Selected');
		echo $form['form'];
	
		echo '<table class="standard" cellspacing="0" cellpadding="0"><tr><th>Option</th><th>Name</th>';
  	
		while($db->next_record()) {
			$row = $db->fetch_row();		
			echo '<tr>';
			echo '<td class="center shrink"><input type="checkbox" name="entry_ids[]" value="' . $row['entry_id'] . '"></td>';
			echo '<td>' . $row['player_name'] . '</td>';
			echo '</tr>';
		}
		
  	echo '</table><br>';
		echo $form['submit'];
		echo '</form><br>';
	
  }
  else {

  	echo 'You are currently accepting all communications.<br>';
  	
  }
  
	echo '<br><h2>Blacklist Player</h2><br>';
	$container = array();
	$container['url'] = 'message_blacklist_add.php';
	$form = create_form($container,'Blacklist');
	echo $form['form'];
	echo '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Name:&nbsp;</td>
			<td class="mb"><input type="text" name="PlayerName" size="30"></td>
	</table><br>
	';
	echo $form['submit'];
	echo '</form>';
	
?>
