<?php

$template->assign('PageTopic','Deleting A Game');

$PHP_OUTPUT.=('What game do u want to delete');

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'game_delete_confirm.php';
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<select name="game_id" id="InputFields">');
$PHP_OUTPUT.=('<option value=None selected>[Select the game]</option>');

$db->query('SELECT * FROM game');
while($db->nextRecord()) {

	//check to see if it needs to be deleted
    $id_game = $db->getField('game_id');
    $name = $db->getField('game_name');

	$PHP_OUTPUT.=('<option value="'.$id_game.'">'.$name.'</option>');

}
$PHP_OUTPUT.=('</select>');

$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Delete');
$PHP_OUTPUT.=('</form>');

?>