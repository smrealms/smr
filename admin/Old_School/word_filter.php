<?php

$smarty->assign('PageTopic','WORD FILTER');

$db = new SMR_DB();

$db->query('SELECT * FROM word_filter');

if(isset($var['error'])) {
	switch($var['error']) {
		case(1):
			echo '<span class="red bold">ERROR: </span>Invalid input.';
			break;
		case(2):
			echo '<span class="yellow">' . strtoupper(trim($_REQUEST['Word'])) . '</span> will now be replaced with <span class="yellow">' . strtoupper(trim($_REQUEST['WordReplacement'])) . '</span>.';
			break;
		case(3):
			echo '<span class="red bold">ERROR: </span>No entries selected for deletion.';
			break;
		default:
			echo '<span class="red bold">ERROR: </span>Unknown error event.';
			break;
	}
	echo '<br><br>';
}
 	
echo '<h2>Filtered Words</h2><br>';
 	
if(!$db->nf()) {
	
	echo 'No words are currently being filtered.<br><br>';
		
}

else {
	
	$container = array();
	$container['url'] = 'word_filter_del.php';
	$form = create_form($container,'Remove Selected');
	echo $form['form'];
		
	echo '<table class="standard" cellspacing="0" cellpadding="0"><tr><th>Option</th><th>Word</th><th>Replacement</th></tr>';
	while($db->next_record()) {
		$row = $db->fetch_row();
		echo '<tr>';
		echo '<td class="center shrink"><input type="checkbox" name="word_ids[]" value="' . $row['word_id'] . '"></td>';
		echo '<td>' . $row['word_value'] . '</td>';
		echo '<td>' . $row['word_replacement'] . '</td>';
		echo '</tr>';
	}
	echo '</table><br>';
	echo $form['submit'];
	echo '</form><br>';
	
} 
 
echo '<h2>Add Word To Filter</h2><br>';
$container = array();
$container['url'] = 'word_filter_add.php';
$form = create_form($container,'Add');
echo $form['form'];
echo '
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Word:&nbsp;</td>
		<td class="mb"><input type="text" name="Word" size="30"></td>
	</tr>
	<tr>
		<td class="top">Replacement:&nbsp;</td>
		<td class="mb"><input type="text" name="WordReplacement" size="30"></td>
	</tr>
</table><br>
';
echo $form['submit'];
echo '</form>';

?>
