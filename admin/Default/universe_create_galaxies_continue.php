<?

$template->assign('PageTopic','CREATE UNIVERSE - ADDING GALAXIES (2/10)');

$galaxy = $var['galaxy'];
$galaxy_idx = $var['galaxy_idx'];

$db->query('SELECT * FROM sector WHERE game_id = ' . $var['game_id'] . ' GROUP BY galaxy_id');
$PHP_OUTPUT.=('<p>'.$galaxy_idx.' / ' . $db->getNumRows() . ' done.</p>');

$container = array();
transfer('game_id');
transfer('galaxy');
transfer('galaxy_idx');

if ($galaxy_idx >= count($galaxy)) {

	$container['url'] = 'skeleton.php';
	$container['body'] = 'universe_create_warps.php';

} else
	$container['url'] = 'universe_create_galaxies_processing.php';

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Continue >>');
$PHP_OUTPUT.=('</form>');

?>