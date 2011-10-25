<?php
$sector =& $player->getSector();

$game_id = $player->getGameID();
//init file so we can get a size later
$file = '';
//function for writing a ushort
function add2bytes($int)
{
	$temp = pack('c', $int % 256);
	$temp .= pack('c', $int / 256);
	return $temp;

}
function addbyte($int)
{
	return pack('c', $int);
}
//generate the players alliance list...
$alliance = '(0';
$db->query('SELECT * FROM player WHERE alliance_id = '.$player->getAllianceID().' AND alliance_id != 0');
while ($db->nextRecord())
{
	$alliance .= ',';
	$alliance .= $db->getField('account_id');
}
$alliance .= ')';
$db->query('SELECT * FROM game WHERE game_id = '.$game_id);
if ($db->nextRecord())
	$game_name = $db->getField('game_name');

$file .= 'CMF by ^V^ Productions ©2004 ';
//get number of galaxies
$galaxies =& SmrGalaxy::getGameGalaxies($game_id);
$file .= addbyte(count($galaxies));
//get galaxy name length
foreach ($galaxies as &$galaxy)
{
	//gal name
	$file .= addbyte(strlen($galaxy->getName()));
	$file .= $galaxy->getName();
	//gal owner
	$file .= addbyte(0);
	//$file .= addbyte(33);
	//$file .= 'Not Supported By SMR Download Yet';
	//default port owner
	if ($db->getField('galaxy_id') <= 8)
		$file .= addbyte($galaxy->getGalaxyID());
	else
		$file .= addbyte(9);
	//gal size
	$file .= addbyte($galaxy->getWidth());
	$file .= addbyte($galaxy->getHeight());
} unset($galaxy);
//planet definitions (num of, [size of name, name])
$file .= addbyte(2);
$file .= addbyte(8);
$file .= 'Friendly';
$file .= addbyte(5);
$file .= 'Enemy';
//done with all header info

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();

$db->query('SELECT * FROM sector WHERE game_id = '.$game_id.' ORDER BY sector_id');
while ($db->nextRecord())
{
	$sector_id = $db->getField('sector_id');

	// link infos
	$CurrByte = 0;
	if ($db->getField('link_up') > 0) $CurrByte += 128;
	if ($db->getField('link_right') > 0) $CurrByte += 64;
	if ($db->getField('link_down') > 0) $CurrByte += 32;
	if ($db->getField('link_left') > 0) $CurrByte += 16;
	//do we have a planet here?
	$db2->query('SELECT * FROM planet WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id);
	if ($db2->nextRecord())
	{
		$CurrByte += 8;
	}
	//do we have a port here?
	$db2->query('SELECT * FROM port WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id);
	if ($db2->nextRecord())
	{
		$CurrByte += 4;
	}
	//sector friendliness
	$db2->query('SELECT * FROM sector_has_forces WHERE sector_id = '.$sector_id.' AND mines > 0 AND owner_id IN '.$alliance);
	if ($db2->getNumRows() > 0)
	{
		//we want a green 'friendly' sector
		$CurrByte += 1;
	}
	else
	{
		//we want a blue 'neutral' sector
		$CurrByte += 0;
	}
	$file .= addbyte($CurrByte);
	$db2->query('SELECT * FROM port WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id);
	$race = 0;
	$has_port = FALSE;
	if ($db2->nextRecord())
	{
		$has_port = TRUE;
		$race = $db2->getField('race_id');
		if ($race == 1) $race = 9;
		else $race -= 1;
		//3 bytes total...
		$db2->query('SELECT * FROM good ORDER BY good_id');
		for ($i=0;$i<=2;$i++)
		{
			$CurrByte = 0;
			$CurrAdd = 128;
			for ($j=0;$j<=3;$j++)
			{
				$db2->nextRecord();
				$good_id = $db2->getField('good_id');
				$db3->query('SELECT * FROM port_has_goods WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id.' AND good_id = '.$good_id);
				if ($db3->nextRecord())
					if ($db3->getField('transaction') == 'Sell')
						$CurrByte += $CurrAdd;
					else
						$CurrByte += $CurrAdd / 2;
				$CurrAdd /= 4;
			}
			$file .= addbyte($CurrByte);
		}
	}
	//add port race byte...
	$race = $race * 16;
	$db2->query('SELECT * FROM planet WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id);
	if ($db2->nextRecord())
	{
		$db2->query('SELECT * FROM planet WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id.' AND owner_id IN '.$alliance);
		if ($db2->nextRecord())
		{
			//friendly planet
			$planet = 1;
			//get level (start at 0)
			$level = 0;
			$db2->query('SELECT * FROM planet_has_building WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id);
			while ($db2->nextRecord())
			{
				$level += $db2->getField('amount');
			}
		}
		else
		{
			//unfriendly/enemy
			$planet = 2;
			//unknown level
			$level = 0;
		}
	}
	else
	{
		//no planet
		$planet = 0;
		$level = 0;
	}
	if ($planet > 0 || $race > 0)
		$file .= addbyte(($planet + $race));
	if ($planet > 0)
		$file .= addbyte($level);
	$db3->query('SELECT * FROM warp WHERE game_id = '.$game_id.' AND (sector_id_1 = '.$sector_id.' OR sector_id_2 = '.$sector_id.')');
	if ($db3->nextRecord())
		$CurrByte = 128;
	else $CurrByte = 0;
	// locations
	$db2->query('SELECT * FROM location NATURAL JOIN location_type WHERE game_id = '.$game_id.' AND sector_id = '.$sector_id);
	$CurrByte += $db2->getNumRows();
	$file .= addbyte($CurrByte);
	// warp
	$db3->query('SELECT * FROM warp WHERE game_id = '.$game_id.' AND (sector_id_1 = '.$sector_id.' OR sector_id_2 = '.$sector_id.')');
	if ($db3->nextRecord())
	{
		$warp_id = ($db3->getField('sector_id_1') == $sector_id) ? $db3->getField('sector_id_2') : $db3->getField('sector_id_1');
		$file .= add2bytes($warp_id);
	}

	while ($db2->nextRecord())
	{
		$file .= add2bytes($db2->getField('mgu_id'));
	}
}

header('Content-type: application/octet-stream'.EOL);
header('Content-Disposition: attachment; filename='.$game_name.'.cmf'.EOL);
header('Content-transfer-encoding: binary'.EOL);
$size = strlen($file);
header('Content-Length: '.$size);
echo($file);

?>