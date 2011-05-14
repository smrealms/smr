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

$db->query('
SELECT sector_id, owner_id
FROM sector_has_forces
WHERE owner_id = '.$player->getAccountID().'
AND game_id = '.SmrSession::$game_id.'
AND expire_time >= '.TIME.'
ORDER BY '.$categorySQL.', '.$subcategory);
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
		$forces =& SmrForce::getForce(SmrSession::$game_id, $db->getField('sector_id'), $db->getField('owner_id'));
		
		$PHP_OUTPUT .= '<tr>';
		$PHP_OUTPUT .= '<td class="shrink noWrap">'.$forces->getSectorID().' ('.$forces->getGalaxy()->getName().')</td>';
		$PHP_OUTPUT .= '<td class="shrink center">'.$forces->getCDs().'</td>';
		$PHP_OUTPUT .= '<td class="shrink center">'.$forces->getSDs().'</td>';
		$PHP_OUTPUT .= '<td class="shrink center">'.$forces->getMines().'</td>';
		$PHP_OUTPUT .= '<td class="shrink noWrap">' . date(DATE_FULL_SHORT, $forces->getExpire()) . '</td>';
		$PHP_OUTPUT .= '</tr>';
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