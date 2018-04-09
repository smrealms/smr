<?php
require_once(get_file_loc('SmrPlanet.class.inc'));
$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$game_id = $_REQUEST['game_id'];
//first get file name
$file = Globals::getGameName($game_id) . '.txt';
//we need to make a file for the SMC thing.
header('Content-Type: text/plain; charset=ISO-8859-1');
header('Content-Disposition: attachment; filename="'.$file.'"');
header('Content-transfer-encoding: base64');

//game heading and info
echo ('[GAME]'.EOL);
echo ($game_id.'='.Globals::getGameName($game_id).EOL);

//get races
echo ('[RACES]'.EOL);
$db->query('SELECT * FROM race ORDER BY race_id');
while ($db->nextRecord()) {
	$id = $db->getField('race_id');
	$name = $db->getField('race_name');
	echo ('R' . $id . '='.$name.EOL);
}

//galaxies
echo ('[GALAXIES]'.EOL);
$i = 1;
$gameGals =& SmrGalaxy::getGameGalaxies($game_id);
foreach($gameGals as &$gameGal) {
	echo ('GAL' . $i . '='.$gameGal->getName().','.$gameGal->getWidth().','.$gameGal->getHeight().EOL);
	$i++;
} unset($gameGal);

//icons
echo ('[ICONS]'.EOL);
echo ('IWood=Wood'.EOL);
echo ('IFood=Food'.EOL);
echo ('IOre =Ore'.EOL);
echo ('IMetl=Metals'.EOL);
echo ('ISlav=Slaves'.EOL);
echo ('IText=Textiles'.EOL);
echo ('IMach=Machinery'.EOL);
echo ('ICirc=Circuits'.EOL);
echo ('IWeap=Weapons'.EOL);
echo ('IComp=Computers'.EOL);
echo ('ILux =Luxuries'.EOL);
echo ('INarc=Narcotics'.EOL);
echo ('IBank=Bank'.EOL);
echo ('IBar=Bar'.EOL);
echo ('IWeap=Weapon shop'.EOL);
echo ('IHard=Hardware shop'.EOL);
echo ('IShip=Ship shop'.EOL);
echo ('IRHQ =Race HQ'.EOL);
echo ('IFHQ =Federal HQ'.EOL);
echo ('IUHQ =Underground HQ'.EOL);
echo ('IFed =Federal Beacon'.EOL);
echo ('ITrad=Trader'.EOL);
echo ('IWarp=Warp'.EOL);
echo ('IPlan=Planet'.EOL);

//goods
echo ('[GOODS]'.EOL);
$db->query('SELECT * FROM good ORDER BY good_id');
while ($db->nextRecord()) {
	$fmv = $db->getField('base_price');
	$name = $db->getField('good_name');
	$id = $db->getField('good_id');
	//assume it is a nuetral good for now
	$align = '0';
	//get evil goods here
	if ($id == 5 || $id == 9 || $id == 12)
		$align = '-';
	echo ('G' . $id . '='.$name.','.$fmv.','.$align.EOL);
}

//ship properties
echo ('[SHIP PROPERTIES]'.EOL);
echo ('SP1=Cost,integer'.EOL);
echo ('SP2=Holds,potential'.EOL);
echo ('SP3=Armour,integer'.EOL);
echo ('SP4=Shields,integer'.EOL);
echo ('SP5=Combat drones,potential'.EOL);
echo ('SP6=Scout drones,potential'.EOL);
echo ('SP7=Mines,potential'.EOL);
echo ('SP8=Hardpoints,integer'.EOL);
echo ('SP9=MR,integer'.EOL);
echo ('SP10=Scanner,bool'.EOL);
echo ('SP11=Illusion generator,bool'.EOL);
echo ('SP12=Cloak,bool'.EOL);
echo ('SP13=Jumpdrive,bool'.EOL);
echo ('SP14=DCS,bool'.EOL);


//ships
echo ('[SHIPS]'.EOL);
$db->query('SELECT * FROM ship_type ORDER BY ship_type_id');
while ($db->nextRecord()) {
	$id = $db->getField('ship_type_id');
	$name = $db->getField('ship_name');
	$race_id = 'R' . $db->getField('race_id');
	$res = $db->getField('buyer_restriction');
	if ($res == 1)
		$align = '+';
	elseif ($res == 2)
		$align = '-';
	else
		$align = '0';
	$speed = $db->getField('speed');
	$cost = $db->getField('cost');
	$hard = $db->getField('hardpoint');
	//assuem 10 for now its not implemented
	$mr = 10;
	$db3->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
	$props = array();
	while ($db3->nextRecord()) {
		$hard_id = $db3->getField('hardware_type_id');
		$db2->query('SELECT * FROM ship_type_support_hardware WHERE ship_type_id = '.$id.' ORDER BY hardware_type_id AND hardware_type_id = '.$hard_id);
		while ($db2->nextRecord())
			$props[$hard_id] = $db2->getField('max_amount');
	}
	$shields = $props[HARDWARE_SHIELDS];
	$armour = $props[HARDWARE_ARMOUR];
	$cargo = $props[HARDWARE_CARGO];
	$combat = $props[HARDWARE_COMBAT];
	$scouts = $props[HARDWARE_SCOUT];
	$mines = $props[HARDWARE_MINE];
	$scanner = $props[HARDWARE_SCANNER];
	$cloak = $props[HARDWARE_CLOAK];
	$illus = $props[HARDWARE_ILLUSION];
	$jump = $props[HARDWARE_JUMP];
	$dcs = $props[HARDWARE_DCS];
	echo ('SHIP' . $id . '='.$name.','.$race_id.','.$align.','.$speed.','.$cost.','.$cargo.','.$armour.','.$shields.','.$combat.','.$scouts.','.$mines.','.$hard.','.$mr.','.$scanner.','.$illus.','.$cloak.','.$jump.','.$dcs.EOL);
	
}

//weapons
echo ('[WEAPONS]'.EOL);
$db->query('SELECT * FROM weapon_type ORDER BY weapon_type_id');
while ($db->nextRecord()) {
	$id = $db->getField('weapon_type_id');
	$name = $db->getField('weapon_name');
	$res = $db->getField('buyer_restriction');
	if ($res == 1)
		$align = '+';
	elseif ($res == 2)
		$align = '-';
	else
		$align = '0';
	$race_id = 'R' . $db->getField('race_id');
	$cost = $db->getField('cost');
	$shi_dam = $db->getField('shield_damage');
	$arm_dam = $db->getField('armour_damage');
	$acc = $db->getField('accuracy');
	$power = $db->getField('power_level');
	echo ('WEP' . $id . '='.$name.','.$race_id.','.$align.','.$cost.','.$shi_dam.','.$arm_dam.','.$acc.','.$power.EOL);
}

//items
echo ('[ITEMS]'.EOL);
$db->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
while ($db->nextRecord()) {
	$name = $db->getField('hardware_name');
	$id = $db->getField('hardware_type_id');
	echo ('ITEM' . $id . '='.$name.EOL);
}

//locations & what they sell
echo ('[LOCATIONS]'.EOL);
$db->query('SELECT * FROM location_type ORDER BY location_type_id');
while ($db->nextRecord()) {
	//set amount of things it sells to 0 for comma reasons
	$amount = 0;
	$id = $db->getField('location_type_id');
	$name = $db->getField('location_name');
	$loc_proc = $db->getField('location_processor');
	if ($loc_proc == 'shop_weapon.php')
		$icon = 'IWeap';
	elseif ($loc_proc == 'shop_shop.php')
		$icon = 'IShip';
	elseif ($id == 101)
		$icon = 'IFHQ';
	elseif ($id == 102)
		$icon = 'IUHQ';
	elseif ($id == 201)
		$icon = 'IFed';
	elseif ($loc_proc == 'shop_hardware.php')
		$icon = 'IHard';
	elseif ($loc_proc == 'bank_personal.php')
		$icon = 'IBank';
	elseif ($loc_proc == 'bar_opening.php')
		$icon = 'IBar';
	elseif ($loc_proc == 'government.php')
		$icon = 'IRHQ';
	//first part of line
	echo ('LOC' . $id . '='.$name.','.$icon);
	//now do we have locations
	$db2->query('SELECT * FROM location_sells_hardware WHERE location_type_id = '.$id);
	while ($db2->nextRecord()) {
		$hard_id = $db2->getField('hardware_type_id');
		$add = 'ITEM' . $hard_id;
		echo (',$add');
		$amount += 1;
	}
	$db2->query('SELECT * FROM location_sells_ships WHERE location_type_id = '.$id);
	while ($db2->nextRecord()) {
		$hard_id = $db2->getField('ship_type_id');
		$add = 'SHIP' . $hard_id;
		echo (','.$add);
		$amount += 1;
	}
	$db2->query('SELECT * FROM location_sells_weapons WHERE location_type_id = '.$id);
	while ($db2->nextRecord()) {
		$hard_id = $db2->getField('weapon_type_id');
		$add = 'WEP' . $hard_id;
		echo (','.$add);
		$amount += 1;
	}
	//do we need a comma?
	if ($amount == 0)
		echo (',');
	//next line
	echo (EOL);
}

//now sectors
echo ('[SECTORS]'.EOL);
$db->query('SELECT * FROM sector WHERE game_id = '.$game_id.' ORDER BY sector_id');
while ($db->nextRecord()) {
	$id = $db->getField('sector_id');
	//right now assume they visited now...since we have no ay of telling the last visit
	$now = date(DATE_FULL_SHORT, TIME);
	$timestamp = $now;
	echo ($id.'='.$timestamp);
	if ($db->getField('link_up') > 0)
		echo ('N');
	if ($db->getField('link_right') > 0)
		echo ('E');
	if ($db->getField('link_down') > 0)
		echo ('S');
	if ($db->getField('link_left') > 0)
		echo ('W');
	echo (',');
	$db2->query('SELECT warp FROM sector WHERE warp != 0 AND game_id = '.$game_id.' AND sector_id = '.$id);
	if ($db2->nextRecord()) {
		$warp = $db2->getField('warp');
		echo ($warp);
	}
	echo (',');
	$db2->query('SELECT * FROM port WHERE game_id = '.$game_id.' AND sector_id = '.$id);
	if ($db2->nextRecord()) {
		$port_race_id = 'R' . $db2->getField('race_id');
		$port_lvl = $db2->getField('level');
	}
	if (isset($port_race_id)) {
		echo ($port_race_id.':'.$port_lvl);
		$db3->query('SELECT * FROM port_has_goods WHERE game_id = '.$game_id.' AND sector_id = '.$id.' ORDER BY good_id');
		while ($db3->nextRecord()) {
			$good_id = $db3->getField('good_id');
			$trans = $db3->getField('transaction');
			if ($trans == 'Buy')
				echo ('-G' . $good_id);
			else
				echo ('+G' . $good_id);
		}
	}
	//get rid of the variables so we dont mistake them for next sector
	unset($port_race_id, $port_lvl, $good_id, $trans);
	echo (',');
	$db2->query('SELECT * FROM location WHERE game_id = '.$game_id.' AND sector_id = '.$id);
	$amount = 0;
	while ($db2->nextRecord()) {
		$loc_id = $db2->getField('location_type_id');
		$add = 'LOC' . $loc_id;
		if ($amount > 0)
			echo ('+');
		echo ($add);
		$amount += 1;
	}
	echo (',');
	$db2->query('SELECT * FROM planet WHERE game_id = '.$game_id.' AND sector_id = '.$id);
	if ($db2->nextRecord()) {
		$planet =& SmrPlanet::getPlanet($game_id,$id);
		$level = $planet->getLevel();
		$owner = $planet->getOwnerID();
		$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' AND account_id = '.$owner);
		$db2->nextRecord();
		$all_id = $db2->getField('alliance_id');
		if ($all_id > 0) {
			$db2->query('SELECT * FROM alliance WHERE game_id = '.$game_id.' AND alliance_id = '.$all_id);
			$db2->nextRecord();
			$alliance = stripslashes($db2->getField('alliance_name'));
		} else
			$alliance = 'None';
		echo ($level.':'.$alliance);
	}
	echo (EOL);
		
}
