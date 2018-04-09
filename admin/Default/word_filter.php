<?php

$template->assign('PageTopic','Word Filter');

$db = new SmrMySqlDatabase();

$db->query('SELECT * FROM word_filter');

if(isset($var['error'])) {
	switch($var['error']) {
		case(1):
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>Invalid input.';
			break;
		case(2):
			$PHP_OUTPUT.= '<span class="yellow">' . strtoupper(trim($_REQUEST['Word'])) . '</span> will now be replaced with <span class="yellow">' . strtoupper(trim($_REQUEST['WordReplacement'])) . '</span>.';
			break;
		case(3):
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>No entries selected for deletion.';
			break;
		default:
			$PHP_OUTPUT.= '<span class="red bold">ERROR: </span>Unknown error event.';
			break;
	}
	$PHP_OUTPUT.= '<br /><br />';
}
 	
$PHP_OUTPUT.= '<h2>Filtered Words</h2><br />';
 	
if(!$db->getNumRows()) {
	
	$PHP_OUTPUT.= 'No words are currently being filtered.<br /><br />';
		
}

else {
	
	$container = array();
	$container['url'] = 'word_filter_del.php';
	$form = create_form($container,'Remove Selected');
	$PHP_OUTPUT.= $form['form'];
		
	$PHP_OUTPUT.= '<table class="standard"><tr><th>Option</th><th>Word</th><th>Replacement</th></tr>';
	while($db->nextRecord()) {
		$row = $db->getRow();
		$PHP_OUTPUT.= '<tr>';
		$PHP_OUTPUT.= '<td class="center shrink"><input type="checkbox" name="word_ids[]" value="' . $row['word_id'] . '"></td>';
		$PHP_OUTPUT.= '<td>' . $row['word_value'] . '</td>';
		$PHP_OUTPUT.= '<td>' . $row['word_replacement'] . '</td>';
		$PHP_OUTPUT.= '</tr>';
	}
	$PHP_OUTPUT.= '</table><br />';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form><br />';
	
} 
 
$PHP_OUTPUT.= '<h2>Add Word To Filter</h2><br />';
$container = array();
$container['url'] = 'word_filter_add.php';
$form = create_form($container,'Add');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Word:&nbsp;</td>
		<td class="mb"><input type="text" name="Word" size="30"></td>
	</tr>
	<tr>
		<td class="top">Replacement:&nbsp;</td>
		<td class="mb"><input type="text" name="WordReplacement" size="30"></td>
	</tr>
</table><br />
';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</form>';
