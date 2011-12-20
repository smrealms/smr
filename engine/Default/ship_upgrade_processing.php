<?php

//////////////////////////////////////////////////
//
//	Script:		ship_upgrade_processing.php
//	Purpose:	Update DB with player selection
//
//////////////////////////////////////////////////

$last_mod = $THIS_PLAYER->getLastShipMod();

$benefits = array();
$sql = query('SELECT * FROM player_has_benefit WHERE ' . $THIS_PLAYER->getSQL());

$used_points = $THIS_SHIP['Points Used'];
$amount = $STAT_INCREASE_TYPES[$var['upgrade_id']]['Static'];

if ($_REQUEST['submit'] == 'Remove') {
	$remove_point_interval = 24 * 3600; //how long between downgrades
	if ($last_mod > (TIME - $remove_point_interval)) {
		$error .= '<span class="red">Error</span> : You can\'t remove more points yet.<br />';
		return;
	}
	//check if they have this upgrade
	if ($THIS_SHIP['Upgrades'][$var['upgrade_id']]==0) {
		$error .= '<span class="red">Error</span> : You don\'t have that upgrade to remove.<br />';
		return;
	}
	
	if ($STAT_INCREASE_TYPES[$var['upgrade_id']]['Upgrade Type'] == 'Hardware') {
	}
	else if ($STAT_INCREASE_TYPES[$var['upgrade_id']]['Upgrade Type'] == 'Weapon') {

		if ($STAT_INCREASE_TYPES[$var['upgrade_id']]['Name'] == 'Max Weapon Level/Power') {
			if ($THIS_SHIP['Power Used'] > ($THIS_SHIP['Ship Power'] - $amount)) {
				$error .= '<span class="red">Error</span> : You must reduce your ship\'s power before that can be safely done.<br />';
				return;
			}
			if ($THIS_SHIP['Highest Power Level Weapons'] > $THIS_SHIP['Highest Weapon Level Allowed'] - $amount) {
				$error .= '<span class="red">Error</span> : You must get rid of your power level ' . 
							$THIS_SHIP['Highest Weapon Level Allowed'] . ' weapons before you can do that.<br />';
				return;
			}
		}
		elseif ($STAT_INCREASE_TYPES[$var['upgrade_id']]['Name'] == 'Max Number Of Weapons') {
			$num_weps = sizeof($THIS_SHIP['Weapons']);
			if ($num_weps > $THIS_SHIP['Max Weps'] - $amount) {
				$error .= '<span class="red">Error</span> : You must get rid of ' . ($num_weps - ($THIS_SHIP['Max Weps'] - $amount)) . ' of your weapons before you do that.<br />';
				return;
			}
		}
	}
	else if ($STAT_INCREASE_TYPES[$var['upgrade_id']]['Upgrade Type'] == 'Gadget') {
		$sql = query('SELECT * FROM player_has_gadget WHERE ' . $THIS_PLAYER->getSQL() . ' AND equipped > 0');
		$num_gads = get_size($sql);
		$remove = $num_gads - ($THIS_SHIP['Gadget Slots'] - 1);
		if ($remove > 0)
			query('UPDATE player_has_gadget SET equipped = 0 WHERE equipped > 0 AND ' . $THIS_PLAYER->getSQL() . ' LIMIT ' . $remove);
	}
	removeBenefit($THIS_SHIP,$THIS_PLAYER,$var['upgrade_id']);
}
elseif ($_REQUEST['submit'] == 'Add') {
	//determine how many points we have
	$total_points = $THIS_SHIP['Ship Size'] + $THIS_SHIP['Upgrade Points'];

		
	$cost_add = getModifiedUpgradeCost($THIS_SHIP,$var['upgrade_id']);
	if ($cost_add > $total_points - $used_points) {
		$error .= '<span class="red">Error</span> : You don\'t have enough points for that.<br />';
		return;
	}
	addBenefit($THIS_SHIP,$var['upgrade_id']);
	query('INSERT INTO player_bought (time, game_id, account_id, type, type_id) VALUES ('.TIME.','.$GAME_ID.','.$ACCOUNT_ID.',\'Upgrade\','.$var['upgrade_id'].')');
}

?>