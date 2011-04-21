<?php

$template->assign('PageTopic','View Forces');

//allow for ordering of forces
if (!isset($var['seq']))
	$order = 'ASC';
else
	$order = $var['seq'];

if (!isset($var['category']))
	$category = 'sector_id';
else
	$category = $var['category'];
$categorySQL = $category.' '.$order;

if (!isset($var['subcategory']))
	$subcategory = 'expire_time ASC';
else
	$subcategory = $var['subcategory'];

$db->query('SELECT * FROM sector_has_forces WHERE owner_id = '.$player->getAccountID().' AND game_id = '.SmrSession::$game_id.' ORDER BY '.$categorySQL.', '.$subcategory);
$db2 = new SmrMySqlDatabase();
if ($db->getNumRows() > 0) {
	
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'forces_list.php';
	if ($order == 'ASC')
		$container['seq'] = 'DESC';
	else
		$container['seq'] = 'ASC';
	$container['subcategory'] = $category;
	
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	setCategories($container,'sector_id',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.=('<th align="center">');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Sector ID</span>');
	$PHP_OUTPUT.=('</th>');
	setCategories($container,'combat_drones',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.=('<th align="center">');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Combat Drones</span>');
	$PHP_OUTPUT.=('</th>');
	setCategories($container,'scout_drones',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.=('<th align="center">');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Scout Drones</span>');
	$PHP_OUTPUT.=('</th>');
	setCategories($container,'mines',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.=('<th align="center">');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Mines</span>');
	$PHP_OUTPUT.=('</th>');
	setCategories($container,'expire_time',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.=('<th align="center">');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Expire time</span>');
	$PHP_OUTPUT.=('</th>');
	$PHP_OUTPUT.=('</tr>');
	
	while ($db->nextRecord())
	{
		$forceGalaxy =& SmrGalaxy::getGalaxyContaining($db->getField('game_id'),$db->getField('sector_id'));
		
		$force_sector	= $db->getField('sector_id');
		$force_sd		= $db->getField('scout_drones');
		$force_cd		= $db->getField('combat_drones');
		$force_mine		= $db->getField('mines');
		$force_time		= $db->getField('expire_time');
		
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$force_sector.' ('.($forceGalaxy===null?'None':$forceGalaxy->getName()).')</td>');
		$PHP_OUTPUT.=('<td align="center">'.$force_cd.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$force_sd.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$force_mine.'</td>');
		$PHP_OUTPUT.=('<td align="center">' . date(DATE_FULL_SHORT, $force_time) . '</td>');
		$PHP_OUTPUT.=('</tr>');
		
	}
	
	$PHP_OUTPUT.=('</table>');
}

else
	$PHP_OUTPUT.=('You have no deployed forces');


function setCategories(&$container,$newCategory,$oldCategory,$oldCategorySQL,$subcategory)
{
    $container['category'] = $newCategory;
    if($oldCategory==$container['category'])
		$container['subcategory'] = $subcategory;
    else
		$container['subcategory'] = $oldCategorySQL;
}
?>