<?php

//this gets us around the universe problem temporarly so I can add stuff to universe.
$container = array();
$container['url'] = 'universe_loop_proc.php';
$PHP_OUTPUT.=('What game<br />');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="game_id" size="1" id="InputFields">');

$db->query('SELECT * FROM game ORDER BY game_id');

while ($db->nextRecord()) {
	$PHP_OUTPUT.=('<option value="' . $db->getField('game_id') . '">' . $db->getField('game_name') . '</option>');
}

$PHP_OUTPUT.=('</select>');
$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('</form>');

?>