<?

$smarty->assign('PageTopic','VIEW FORCES');

//allow for ordering of forces
if (!isset($var['seq']))
    $order = 'ASC';
else
    $order = $var['seq'];
    
if (!isset($var['category']))
    $category = 'sector_id';
else
    $category = $var['category'];
$db->query('SELECT * FROM sector_has_forces WHERE owner_id = '.$player->getAccountID().' AND game_id = '.SmrSession::$game_id.' ORDER BY '.$category.' '.$order);
$db2 = new SmrMySqlDatabase();
if ($db->getNumRows() > 0) {
	
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'forces_list.php';
	if ($order == 'ASC')
		$container['seq'] = 'DESC';
    else
        $container['seq'] = 'ASC';
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$container['category'] = 'sector_id';
    $PHP_OUTPUT.=('<th align="center">');
    $PHP_OUTPUT.=create_link($container, '<span style="color:#80C870;">Sector ID</span>');
	$PHP_OUTPUT.=('</th>');
	$container['category'] = 'combat_drones';
    $PHP_OUTPUT.=('<th align="center">');
    $PHP_OUTPUT.=create_link($container, '<span style="color:#80C870;">Combat Drones</span>');
	$PHP_OUTPUT.=('</th>');
	$container['category'] = 'scout_drones';
    $PHP_OUTPUT.=('<th align="center">');
    $PHP_OUTPUT.=create_link($container, '<span style="color:#80C870;">Scout Drones</span>');
	$PHP_OUTPUT.=('</th>');
	$container['category'] = 'mines';
    $PHP_OUTPUT.=('<th align="center">');
    $PHP_OUTPUT.=create_link($container, '<span style="color:#80C870;">Mines</span>');
	$PHP_OUTPUT.=('</th>');
	$container['category'] = 'expire_time';
    $PHP_OUTPUT.=('<th align="center">');
    $PHP_OUTPUT.=create_link($container, '<span style="color:#80C870;">Expire time</span>');
	$PHP_OUTPUT.=('</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$force_sector	= $db->getField('sector_id');
		$db2->query('SELECT * FROM sector WHERE sector_id = '.$force_sector.' AND game_id = '.$player->getGameID());
		$db2->nextRecord();
		$gal_id			= $db2->getField('galaxy_id');
		$db2->query('SELECT * FROM galaxy WHERE galaxy_id = '.$gal_id);
		$db2->nextRecord();
		$galaxy_name = $db2->getField('galaxy_name');
		$force_sd		= $db->getField('scout_drones');
		$force_cd		= $db->getField('combat_drones');
		$force_mine		= $db->getField('mines');
		$force_time		= $db->getField('expire_time');
		
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$force_sector.' ('.$galaxy_name.')</td>');
		$PHP_OUTPUT.=('<td align="center">'.$force_cd.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$force_sd.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$force_mine.'</td>');
		$PHP_OUTPUT.=('<td align="center">' . date('n/j/Y g:i:s A', $force_time) . '</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');
}

else
	$PHP_OUTPUT.=('You have no deployed forces');

?>