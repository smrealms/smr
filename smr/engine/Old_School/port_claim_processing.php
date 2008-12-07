<?

$db->query('UPDATE port SET race_id = '.$player->getRaceID().' WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID());

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'port_loot.php';
forward($container);

?>